<?php
return [
    'telegram' => 'sweepstake_s',
    'group_name' => 'Розыгрыши и конкурсы',
    'api_id' => '28022471',                                 // Данные получаем  на https://my.telegram.org
    'api_hash' => '1665e36b7cd6313a4468876f7bf875c3',


     // Update existing data?
    'updating_messages' => false,


     // The method of obtaining data. Accepted values 'database' or 'api'
    'data_source' => 'database',


     // Data to connect to the database
    'servername' => 'localhost',
    'database' => 'telegram',
    'username' => 'root',
    'password' => 'root',
];
