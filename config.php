<?php
// Both modules read this file. Malinka's Database::connection() uses
// $cfg['db']['pass'] while Arun's App\Core\Database::connect() uses
// $cfg['db']['password'] — keep BOTH keys with the same value.
return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'second_harvest',
        'user'     => 'root',
        'pass'     => '',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'base_url'     => 'http://localhost/Deployment/2nd-harvest/public',
        'session_name' => 'harvest_session',
    ],
];
