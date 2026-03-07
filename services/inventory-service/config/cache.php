<?php

return [

    'default' => env('CACHE_DRIVER', 'redis'),

    'stores' => [

        'array' => [
            'driver'    => 'array',
            'serialize' => false,
        ],

        'redis' => [
            'driver'     => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    'prefix' => env('CACHE_PREFIX', 'inventory_service_cache'),

];
