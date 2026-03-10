<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'order_db'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'orders_db'),
            'username'  => env('DB_USERNAME', 'order_svc'),
            'password'  => env('DB_PASSWORD', 'order_pass'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],
    ],
    'migrations' => 'migrations',
];
