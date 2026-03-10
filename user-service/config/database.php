<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'user_db'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'users_db'),
            'username'  => env('DB_USERNAME', 'user_svc'),
            'password'  => env('DB_PASSWORD', 'user_pass'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],
    ],
    'migrations' => 'migrations',
];
