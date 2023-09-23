<?php
namespace madeline;

use danog\MadelineProto\Exception;
use danog\MadelineProto\StrTools;
use danog\MadelineProto\API;

class MadelineScripts
{
    public $config;
    public $source;

    public function __construct(array $config)
    {
        $this->config = $config;

        $session = $this->connect();

        $messages = $this->getMessages($session, $this->config['telegram'], 0, 20);

        $this->source = $this->getMessagesContent($session, $messages);

        $this->stop($session);
    }

    /**
     * Подключается к API Telegram
     *
     * @return API
     */
    private function connect(): API
    {
        $settings = [
            'app_info' => [
                'api_id' => $this->config['api_id'],
                'api_hash' => $this->config['api_hash'],
            ],
            'logger' => [
                'logger' => 0,
                'logger_level' => 2,
            ],
            'serialization' => [
                'serialization_interval' => 300,
                'cleanup_before_serialization' => false,
            ],
        ];

        $session = new API('session.madeline', $settings);
        $session->start();

        return $session;
    }

    /**
     * Получает сообщения
     *
     * @param API $session
     * @param string $peer
     * @param int $min_id
     * @param int $limit
     *
     * @return array
     */
    public static function getMessages(API $session, string $peer, int $min_id, int $limit): array
    {
        $messages_Messages = $session->messages->getHistory([
            'peer' => $peer,
            'offset_id' => 0,
            'offset_date' => 0,
            'add_offset' => 0,
            'limit' => $limit,
            'max_id' => 0,
            'min_id' => $min_id,
        ]);

        $messages = $messages_Messages['messages'];

        return $messages;
    }

    /**
     * Получает контент переданного сообщения
     *
     * @param API $session
     * @param array $messages
     *
     * @return null | string
     */
    public function getMessagesContent(API $session, array $messages = []) : null | string
    {
        $source = $source ?? null;

        foreach (array_reverse($messages) as $i => $message)
        {
            if (!$message || $message['_'] === 'messageEmpty')
            {
                throw new Exception('Пустое сообщение');
            }

            if (!$this->hasMedia($message, true)) {
               // throw new Exception('В сообщении нет медиа контента');
                continue;
            }

            $this->getMedia($session, $message);

            $source = $this->createMessageView($message, $source);

            unset($message);
        }
        unset($messages);

        return $source;
    }

    /**
     * Проверяет есть ли подходящие медиа у сообщения
     *
     * @param array $message
     * @param bool $allowWebPage
     *
     * @return bool
     */
    public static function hasMedia(array $message = [], bool $allowWebPage = false): bool
    {
        $mediaType = $message['media']['_'] ?? null;
        if ($mediaType === null) {
            return false;
        }
        if (
            $mediaType === 'messageMediaWebPage' &&
            ($allowWebPage === false || empty($message['media']['webpage']['photo']))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Получает медиа-файла переданного сообщения
     *
     * @param API $session
     * @param array $message
     *
     * @return void
     */
    public static function getMedia(API $session, array $message): void
    {
        if (!empty($message['media'])) {
            if (!empty($message['media']['photo'])) {
                $glob = glob('img/' . $message['media']['photo']['id'] . '*.*');
                if (empty($glob[0])) {
                    $session->downloadToDir($message, getcwd() . '/img/');

                    $glob_jpg = glob('img/' . $message['media']['photo']['id'] . '*.jpg');
                    if (!empty($glob_jpg[0])) {
                        self::webpImage($glob_jpg[0]);
                    }
                }
            } elseif (!empty($message['media']['document'])) {
                $glob = glob('img/' . $message['media']['document']['id'] . '*.*');
                if (empty($glob[0]) && self::isSmallFile($message)) {
                    $session->downloadToDir($message, getcwd() . '/img/');
//                } elseif (empty($glob[0]) && !self::isSmallFile($message)) {
//                    $session->downloadToDir($message['media']['document']['thumbs'][0]['bytes'], getcwd() . '/img/');

                    // Преобразовываем скаченный файл в .webl и удаляем исходник
//                    $glob_jpg = glob('img/' . $message['media']['document']['id'] . '*.jpg');
//                    if (!empty($glob_jpg[0])) {
//                        self::webpImage($glob_jpg[0]);
//                    }
                }
            }
        }
    }

    /**
     * Создаёт структуру сообщения для передачи в представление
     *
     * @param array $message
     * @param null | string $source
     *
     * @return string
     */
    private function createMessageView(array $message, null | string $source) : string
    {
        $source .= "<div class='message_container'>";
        $source .= "<div class='message_title'><a href='https://t.me/" . $this->config['telegram'] . "' target='_blank'>" . $this->config['group_name'] . "</a></div>";

        // Если в сообщение есть изображение, добавляем его к выводу
        if (!empty($message['media']['photo'])) {
            $photoID = $message['media']['photo']['id'];
            $photoSrc = glob('img/' . $photoID . '*.webp');
            if (!empty($photoID)) {
                $source .= "<div class='message_img'><a href='https://t.me/" . $this->config['telegram'] . "/" . $message['id'] . "' target='_blank'><img src='" . $photoSrc[0] . "' class='img_width'></a></div>";
            }
        }

        // Если в сообщение есть видео, добавляем его к выводу
        if (!empty($message['media']['document'])) {
            $videoId = $message['media']['document']['id'];
            if (MadelineScripts::isSmallFile($message)) {
                $videoSrc = glob('img/*' . $videoId . '*.mp4');
                $mimeType = $message['media']['document']['mime_type'] ?: 'video/mp4';
                $source .= "<div class='message_video'>
                                    <video class='header__background-inner' width='500px' preload='' muted='' autoplay='' loop='' playsinline='' id='" . $videoId . "'>
                                        <source src='" . $videoSrc[0] . "' type='" . $mimeType . "'>
                                    </video>
                                  </div>";
            } else {
                $source .= "<div class='message_big_video'>
                                        <div class='text_big_video'>Видео очень большое.</div>
                                        <div class='link_big_video'><a href='https://t.me/" . $this->config['telegram'] . "/" . $message['id'] . "'>Посмотреть в Telegram</a></div>
                                     </div>";
            }
        }

        // Если в сообщение есть форматирование, добавляем его к выводу
        if (!empty($message['entities'])) {
            $entities = $message['entities'];
            $message['message'] = $this->formatMessage($message['message'], $entities);
        }

        // Подвал сообщения
        $source .= "<div class='message'><pre>" . $message['message'] . "</pre></div>";
        if (isset($message['entities'][0]['url'])) {
            $source .= "<div class='message_url'><a href='" . $message['entities'][0]['url'] . "' target='_blank'>" . $message['entities'][0]['url'] . "</a></div>";
        }
        $source .= "<div class='message_bottom'><div class='bottom_block'><a href='https://t.me/" . $this->config['telegram'] . "/" . $message['id'] . "' target='_blank'>t.me/" . $this->config['telegram'] . "/" . $message['id'] . "</a></div><div class='bottom_block_right'><div class='message_view'>" . $message['views'] . "<svg class='view_icon' version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
                     viewBox='0 0 42 42' enable-background='new 0 0 42 42' xml:space='reserve'>
                <path d='M15.3,20.1c0,3.1,2.6,5.7,5.7,5.7s5.7-2.6,5.7-5.7s-2.6-5.7-5.7-5.7S15.3,17,15.3,20.1z M23.4,32.4
                    C30.1,30.9,40.5,22,40.5,22s-7.7-12-18-13.3c-0.6-0.1-2.6-0.1-3-0.1c-10,1-18,13.7-18,13.7s8.7,8.6,17,9.9
                    C19.4,32.6,22.4,32.6,23.4,32.4z M11.1,20.7c0-5.2,4.4-9.4,9.9-9.4s9.9,4.2,9.9,9.4S26.5,30,21,30S11.1,25.8,11.1,20.7z'/>
                </svg></div><span>" . date('j F Y H:i', $message['date']) . "</span></div></div>";
        $source .= "</div>";

        return $source;
    }

    /**
     * Форматирует текст
     *
     * @param string $message
     * @param array $entities
     *
     * @return string
     */
    public function formatMessage(string $message = null, array $entities = []): ?string
    {
        $html = [
            'messageEntityItalic' => '<i>%s</i>',
            'messageEntityBold' => '<strong>%s</strong>',
            'messageEntityCode' => '<code>%s</code>',
            'messageEntityPre' => '<pre>%s</pre>',
            'messageEntityStrike' => '<strike>%s</strike>',
            'messageEntityUnderline' => '<u>%s</u>',
            'messageEntityBlockquote' => '<blockquote>%s</blockquote>',
            'messageEntityTextUrl' => '<a href="%s" target="_blank" rel="nofollow">%s</a>',
            'messageEntityMention' => '<a href="tg://resolve?domain=%s" rel="nofollow">%s</a>',
            'messageEntityUrl' => '<a href="%s" target="_blank" rel="nofollow">%s</a>',
        ];

        foreach ($entities as $key => &$entity) {
            if (isset($html[$entity['_']])) {

                $text = StrTools::mbSubstr($message, $entity['offset'], $entity['length']);

                $template = $html[$entity['_']];
                if (in_array($entity['_'], ['messageEntityTextUrl', 'messageEntityMention', 'messageEntityUrl'])) {
                    $textFormated = sprintf($template, strip_tags($entity['url'] ?? $text), $text);
                } else {
                    $textFormated = sprintf($template, $text);
                }

                $message = static::substringReplace($message, $textFormated, $entity['offset'], $entity['length']);

                //Увеличим оффсеты всех следующих entity
                foreach ($entities as $nextKey => &$nextEntity) {
                    if ($nextKey <= $key) {
                        continue;
                    }
                    if ($nextEntity['offset'] < ($entity['offset'] + $entity['length'])) {
                        $nextEntity['offset'] += StrTools::mbStrlen(
                            preg_replace('~(\>).*<\/.*$~', '$1', $textFormated)
                        );
                    } else {
                        $nextEntity['offset'] += StrTools::mbStrlen($textFormated) - StrTools::mbStrlen($text);
                    }
                }
                unset($nextEntity);
            }
        }
        unset($entity);
        $message = nl2br($message);
        return $message;
    }

    private static function substringReplace(string $original, string $replacement, int $position, int $length): string
    {
        $startString = StrTools::mbSubstr($original, 0, $position);
        $endString = StrTools::mbSubstr($original, $position + $length, StrTools::mbStrlen($original));
        return $startString . $replacement . $endString;
    }

    /**
     * Разрывает сессею с API Telegram
     *
     * @return void
     */
    private function stop($session): void
    {
        $session->stop();
    }

    /**
     * Проверяет не превышает ли размер файла указанного значения
     *
     * @param array $message
     *
     * @return bool
     */
    public static function isSmallFile(array $message = []) : bool
    {
        $size = $message['media']['document']['size'];
        return $size < 1048576 ? true : false;
    }

    /**
     * Преобразовывает изображения в формат .webp
     *
     * @param string $source
     * @param int $quality
     * @param bool $removeOld
     *
     * @return string
     */
    private static function webpImage(string $source = '', int $quality = 50, bool $removeOld = true): string
    {
        $dir = pathinfo($source, PATHINFO_DIRNAME);
        $name = pathinfo($source, PATHINFO_FILENAME);
        $destination = $dir . DIRECTORY_SEPARATOR . $name . '.webp';
        $info = getimagesize($source);
        $isAlpha = false;
        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);
        elseif ($isAlpha = $info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($isAlpha = $info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } else {
            return $source;
        }
        if ($isAlpha) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
        imagewebp($image, $destination, $quality);

        if ($removeOld)
            unlink($source);

        return $destination;
    }
}