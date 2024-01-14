<?php
// Подключаем библиотеку MadelineProto
require_once ('src/madeline/MadelineScripts.php');
require_once ('vendor/autoload.php');
require_once('src/DataBase.php');

use madeline\MadelineScripts;

$config = require 'config/config.php';
$madeline = new MadelineScripts($config);
$source = $madeline->source;


require_once ('templates/template.php');