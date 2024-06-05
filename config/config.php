<?php
return [
    'telegram' => 'sweepstake_s',
    'group_name' => 'Розыгрыши и конкурсы',
    'api_id' => '',                                 // Данные получаем  на https://my.telegram.org
    'api_hash' => '',


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
