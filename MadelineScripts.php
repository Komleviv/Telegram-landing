<?php
class MadelineScripts
{
// функция подключения к API Telegram
   public static function connect()
    {
        $settings = [
            'app_info' => [ // Эти данные мы получили после регистрации приложения на https://my.telegram.org
                'api_id' => '28022471',
                'api_hash' => '1665e36b7cd6313a4468876f7bf875c3',
            ],
            'logger' => [ // Вывод сообщений и ошибок
                'logger' => 0, // не выводим ошибки (?)
                'logger_level' => 2, // выводим только критические ошибки.
            ],
            'serialization' => [
                'serialization_interval' => 300,
                'cleanup_before_serialization' => false,
            ],
        ];

        $session = new \danog\MadelineProto\API('session.madeline', $settings);
        $session->start();

        return $session;
    }

// Функция получения сообщений
    public static function getMessages($session, $peer, $min_id, $limit)
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

// Функция получения медиа-файла конкретного сообщения
    public static function getMedia($session, $message)
    {
        // Если информация о сообщение содержит медиа-файл, сохраняем его в выбранную категорию
        if (!empty($message['media'])) {

            // Если медиа - это изображение
            if (!empty($message['media']['photo'])) {

                // Проверяем существования файла с таким id.
                $glob = glob('img/' . $message['media']['photo']['id'] . '*.*');
                if (empty($glob[0])) {
                    // Если файла нет, скачиваем его
                    $session->downloadToDir($message, getcwd() . '/img/');

                    // Преобразовываем скаченный файл в .webl и удаляем исходник
                    $glob_jpg = glob('img/' . $message['media']['photo']['id'] . '*.jpg');
                    if (!empty($glob_jpg[0])) {
                        self::webpImage($glob_jpg[0]);
                    }
                }
            } elseif (!empty($message['media']['document'])) {

                // Проверяем существования файла с таким id.
                $glob = glob('img/' . $message['media']['document']['id'] . '*.*');
                if (empty($glob[0]) && self::isSmallFile($message)) {
                    // Если файла нет и его размер меньше 10 Мб, скачиваем его
                    $session->downloadToDir($message, getcwd() . '/img/');
                } elseif (empty($glob[0]) && !self::isSmallFile($message)) {
                    $session->downloadToDir($message['media']['document']['thumbs'][0]['inflated'], getcwd() . '/img/');

                    // Преобразовываем скаченный файл в .webl и удаляем исходник
                    $glob_jpg = glob('img/' . $message['media']['document']['id'] . '*.jpg');
                    if (!empty($glob_jpg[0])) {
                        self::webpImage($glob_jpg[0]);
                    }
                }
            }
        }
    }

    public static function stop($session)
    {
        $session->stop();
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

    public static function isSmallFile(array $message = []) : bool
    {
        $size = $message['media']['document']['size'];
        return $size < 1048576 ? true : false;
    }

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

                $text = static::mbSubstr($message, $entity['offset'], $entity['length']);

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
                        $nextEntity['offset'] += static::mbStrlen(
                            preg_replace('~(\>).*<\/.*$~', '$1', $textFormated)
                        );
                    } else {
                        $nextEntity['offset'] += static::mbStrlen($textFormated) - static::mbStrlen($text);
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
        $startString = static::mbSubstr($original, 0, $position);
        $endString = static::mbSubstr($original, $position + $length, static::mbStrlen($original));
        return $startString . $replacement . $endString;
    }

// Функция преобразования изображений в webp
    private static function webpImage($source, $quality = 50, $removeOld = true)
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