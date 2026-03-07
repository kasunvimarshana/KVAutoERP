<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Message Broker Driver
    |--------------------------------------------------------------------------
    | Supported: "null", "rabbitmq", "kafka"
    |
    | Set MESSAGE_BROKER_DRIVER in your .env file to switch drivers at runtime
    | without touching any business logic.
    */

    'driver' => env('MESSAGE_BROKER_DRIVER', 'null'),

    'rabbitmq' => [
        'host'     => env('RABBITMQ_HOST', 'localhost'),
        'port'     => env('RABBITMQ_PORT', 5672),
        'user'     => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost'    => env('RABBITMQ_VHOST', '/'),
    ],

    'kafka' => [
        'brokers'  => env('KAFKA_BROKERS', 'localhost:9092'),
        'group_id' => env('KAFKA_GROUP_ID', 'saas-inventory'),
    ],

];
