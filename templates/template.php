<?php
/** @var $config array */
/** @var $source array */

?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&subset=latin,cyrillic" rel="stylesheet">
        <link rel="stylesheet" href="templates/style.css" type="text/css">
        <title><?= $config['group_name']; ?></title>
    </head>
    <body class="page">
        <div class="telegram_wrapper"></div>
        <div class="telegram_button_container">
            <a class="telegram_button" href="https://t.me/<?= $config['telegram']; ?>" target="_blank" rel="noopener"><div style="height: 28px; width:28px"><svg width="28px" height="28px" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M83.1797 17.5886C83.1797 17.5886 90.5802 14.7028 89.9635 21.711C89.758 24.5968 87.9079 34.6968 86.4688 45.6214L81.5351 77.9827C81.5351 77.9827 81.124 82.7235 77.4237 83.548C73.7233 84.3724 68.173 80.6623 67.145 79.8378C66.3227 79.2195 51.7273 69.9438 46.5878 65.4092C45.1488 64.1724 43.5042 61.6989 46.7934 58.8132L68.3785 38.201C70.8454 35.7274 73.3122 29.956 63.0336 36.9642L34.2535 56.5459C34.2535 56.5459 30.9644 58.6071 24.7973 56.752L11.4351 52.6295C11.4351 52.6295 6.50135 49.5377 14.9298 46.4457C35.4871 36.7579 60.7724 26.864 83.1797 17.5886Z" fill="#fff"></path></svg></div><div class="Telegram_button_text">Telegram</div><div class='telegram_button_arrow'><svg role="presentation" viewBox="0 0 10 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.00071 1L8.27344 9L1.00071 17" stroke="#fff" stroke-width="1"></path></svg></div></a>
        </div>
        <div class='container'>
            <?= $source; ?>
        </div>
    </body>
</html>