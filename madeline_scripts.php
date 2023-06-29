<?php
// Подключаем библиотеку MadelineProto
require_once 'vendor/autoload.php';

// функция подключения к API Telegram 
function connect() { 
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
          //Очищать файл сессии от некритичных данных. 
          //Значительно снижает потребление памяти при интенсивном использовании, но может вызывать проблемы
          'cleanup_before_serialization' => false,
      ],
  ];

  $MadelineProto = new \danog\MadelineProto\API('session.madeline', $settings);
  $MadelineProto->start();
  
  return $MadelineProto;
}

// Функция получения сообщений
function get_messages($peer, $min_id, $limit) {
  $MadelineProto = connect();
  $messages_Messages = $MadelineProto->messages->getHistory([
      'peer' => $peer, 
      'offset_id' => 0, 
      'offset_date' => 0, 
      'add_offset' => 0, 
      'limit' => $limit, 
      'max_id' => 0, 
      'min_id' => $min_id,
  ]);

  $messages = $messages_Messages['messages'];

  $MadelineProto->stop();

  return $messages;
}

// Функция получения медиа-файла конкретного сообщения
function get_images($message) {
  $MadelineProto = connect();
  // Если информация о сообщение содержит медиа-файл, сохраняем его в выбранную категорию
  if (!empty($message['media'])) {
  
    // Если медиа - это изображение
    if (!empty($message['media']['photo'])) {
      
        // Проверяем существования файла с таким id.
        $glob = glob('img/' . $message['media']['photo']['id']  . '*.*');
        if  (empty($glob[0])) {
          // Если файла нет, скачиваем его
          $MadelineProto->downloadToDir($message, getcwd() . '/img/');
          
          // Преобразовываем скаченный файл в .webl и удаляем исходник
          $glob_jpg = glob('img/' . $message['media']['photo']['id']  . '*.jpg');
          if (!empty($glob_jpg[0])) {
            webpImage($glob_jpg[0]);
          }
        }
    } elseif (!empty($message['media']['document'])) {
      
      // Проверяем существования файла с таким id.
      $glob = glob('img/' . $message['media']['document']['id']  . '*.*');
      if  (empty($glob[0])) {

          // Если файла нет, скачиваем его
          $MadelineProto->downloadToDir($message, getcwd() . '/img/');
        }
    }
  }

  $MadelineProto->stop();
}

// Функция преобразования изображений в webp
function webpImage($source, $quality = 50, $removeOld = true)
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

?>