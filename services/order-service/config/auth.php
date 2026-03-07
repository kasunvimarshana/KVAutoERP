<?php

return [
    'defaults' => [
        'guard'     => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver'   => 'passport',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            // Order service validates tokens issued by auth-service;
            // it does not have a local User model, so we use a minimal stub.
            'model'  => App\Domain\Auth\Entities\ServiceUser::class,
        ],
    ],

    'passwords' => [],

    'password_timeout' => 10800,
];
