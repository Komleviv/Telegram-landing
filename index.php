<?php
include 'madeline_scripts.php';

// Адрес Telegram-канала
$telegram = 'smart_deco';

$source = '';
   
$messages = get_messages($telegram, 0, 11);
//var_dump ($messages);
foreach(array_reverse($messages) as $i => $message) { 
//var_dump ($messages);
 $media = get_images($message);
 $source .= "<div class='message_container'>";
 $source .= "<div class='message_title'><a href='https://t.me/". $telegram ."' target='_blank'>Smart-deco</a></div>";
 
 // Если в сообщение есть изображение добавляем его к выводу
 if (!empty($message['media']['photo'])) {
 // Ищем по id картинки .webp файл в каталоге /img
     $photo_id = glob('img/' . $message['media']['photo']['id']  . '*.webp');
     if (!empty($photo_id)) {
        $source .= "<div class='message_img'><a href='https://t.me/". $telegram ."/" . $message['id'] ."' target='_blank'><img src='" . $photo_id[0] ."' class='img_width'></a></div>";
     }
}
  
 // Если в сообщение есть видео добавляем его к выводу
 if (!empty($message['media']['document'])) {
     $source .=  "<div class='message_video'><video class='header__background-inner' width='500px' autoplay='true' loop='true' id='bgvid'>
                    <source src='img/BTCa-2.pdf_5339189363098001938.mp4' type='video/mp4'>
                  </video></div>";
 }
 
  
//   if (!empty($message['entities'])) {
//     foreach ($message['entities'] as $j => $decorate) {
//      $message_string = $message['message'];
//      if ($message['entities'][$j]['_'] == 'messageEntityBold' ) { 
//        for ($st1 = 1; $st1 <= $message['entities'][$j]['offset']; $st1++) {
//         $str1 .= $message_string[$st1];
//        }
//        echo $str1;
// //          $message_decorate = mb_substr($message_string, $message['entities'][$j]['offset']-1,  $message['entities'][$j]['length']) . '<br>';
//      }
//     }
//  }
  
  
  $source .= "<div class='message'><pre>" . $message['message']. "</pre></div>";
 if (isset($message['entities'][0]['url'])) {
   $source .= "<div class='message_url'><a href='". $message['entities'][0]['url'] ."' target='_blank'>" . $message['entities'][0]['url']. "</a></div>";
 }
 $source .= "<div class='message_bottom'><div class='bottom_block'><a href='https://t.me/". $telegram ."/" . $message['id'] ."' target='_blank'>t.me/". $telegram ."/" . $message['id'] ."</a></div><div class='bottom_block_right'><div class='message_view'>" . $message['views'] . "<svg class='view_icon' version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
	 viewBox='0 0 42 42' enable-background='new 0 0 42 42' xml:space='reserve'>
<path d='M15.3,20.1c0,3.1,2.6,5.7,5.7,5.7s5.7-2.6,5.7-5.7s-2.6-5.7-5.7-5.7S15.3,17,15.3,20.1z M23.4,32.4
	C30.1,30.9,40.5,22,40.5,22s-7.7-12-18-13.3c-0.6-0.1-2.6-0.1-3-0.1c-10,1-18,13.7-18,13.7s8.7,8.6,17,9.9
	C19.4,32.6,22.4,32.6,23.4,32.4z M11.1,20.7c0-5.2,4.4-9.4,9.9-9.4s9.9,4.2,9.9,9.4S26.5,30,21,30S11.1,25.8,11.1,20.7z'/>
</svg></div><span>" . date('j F Y H:i', $message['date']) ."</span></div></div>";
  $source .= "</div>";
 }

// require_once ('src/template.php');
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&subset=latin,cyrillic" rel="stylesheet">
    <link rel="stylesheet" href="src/style.css" type="text/css">
    <title>Smart-deco</title>
  </head>
  <body class="page">
    <div class="telegram_wrapper"></div>
      <div class="telegram_button_container">
        <a class="telegram_button" href="https://t.me/<?= $telegram; ?>" target="_blank" rel="noopener"><div style="height: 28px; width:28px"><svg width="28px" height="28px" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M83.1797 17.5886C83.1797 17.5886 90.5802 14.7028 89.9635 21.711C89.758 24.5968 87.9079 34.6968 86.4688 45.6214L81.5351 77.9827C81.5351 77.9827 81.124 82.7235 77.4237 83.548C73.7233 84.3724 68.173 80.6623 67.145 79.8378C66.3227 79.2195 51.7273 69.9438 46.5878 65.4092C45.1488 64.1724 43.5042 61.6989 46.7934 58.8132L68.3785 38.201C70.8454 35.7274 73.3122 29.956 63.0336 36.9642L34.2535 56.5459C34.2535 56.5459 30.9644 58.6071 24.7973 56.752L11.4351 52.6295C11.4351 52.6295 6.50135 49.5377 14.9298 46.4457C35.4871 36.7579 60.7724 26.864 83.1797 17.5886Z" fill="#fff"></path></svg></div><div class="Telegram_button_text">Telegram</div><div class='telegram_button_arrow'><svg role="presentation" viewBox="0 0 10 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.00071 1L8.27344 9L1.00071 17" stroke="#fff" stroke-width="1"></path></svg></div></a>
      </div>
    <div class='container'>
             <?= $source; ?>
    </div>
  </body>
</html>