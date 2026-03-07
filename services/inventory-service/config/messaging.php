<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Message Broker
    |--------------------------------------------------------------------------
    | Supported: "rabbitmq", "kafka"
    */
    'default' => env('MESSAGE_BROKER', 'rabbitmq'),

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ
    |--------------------------------------------------------------------------
    */
    'rabbitmq' => [
        'host'        => env('RABBITMQ_HOST', 'rabbitmq'),
        'port'        => (int) env('RABBITMQ_PORT', 5672),
        'user'        => env('RABBITMQ_USER', 'guest'),
        'password'    => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost'       => env('RABBITMQ_VHOST', '/'),
        'max_retries' => (int) env('RABBITMQ_MAX_RETRIES', 3),
        'retry_delay' => (int) env('RABBITMQ_RETRY_DELAY', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kafka
    |--------------------------------------------------------------------------
    */
    'kafka' => [
        'brokers'     => env('KAFKA_BROKERS', 'kafka:9092'),
        'group_id'    => env('KAFKA_GROUP_ID', 'inventory-service'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Topic / Exchange Names
    |--------------------------------------------------------------------------
    | Centralised mapping so topic names are never hard-coded in services.
    */
    'topics' => [
        'inventory_created' => 'inventory.created',
        'inventory_updated' => 'inventory.updated',
        'inventory_deleted' => 'inventory.deleted',
        'stock_depleted'    => 'inventory.stock.depleted',
        'stock_adjusted'    => 'inventory.stock.adjusted',
        'stock_reserved'    => 'inventory.stock.reserved',
        'stock_released'    => 'inventory.stock.released',
    ],
];
