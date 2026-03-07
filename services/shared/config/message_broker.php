<?php

return [
    'driver' => env('MESSAGE_BROKER_DRIVER', 'database'),

    'connections' => [
        'rabbitmq' => [
            'host'           => env('RABBITMQ_HOST', 'rabbitmq'),
            'port'           => (int) env('RABBITMQ_PORT', 5672),
            'vhost'          => env('RABBITMQ_VHOST', '/'),
            'user'           => env('RABBITMQ_USER', 'guest'),
            'password'       => env('RABBITMQ_PASSWORD', 'guest'),
            'exchange'       => env('RABBITMQ_EXCHANGE', 'saas.events'),
            'exchange_type'  => 'topic',
            'durable'        => true,
            'persistent'     => true,
            'prefetch_count' => 10,
            'heartbeat'      => 60,
        ],

        'kafka' => [
            'brokers'           => env('KAFKA_BROKERS', 'kafka:9092'),
            'consumer_group'    => env('KAFKA_CONSUMER_GROUP', 'saas-default'),
            'auto_offset_reset' => 'earliest',
            'security_protocol' => env('KAFKA_SECURITY_PROTOCOL', 'PLAINTEXT'),
            'sasl_mechanisms'   => env('KAFKA_SASL_MECHANISMS', ''),
            'sasl_username'     => env('KAFKA_SASL_USERNAME', ''),
            'sasl_password'     => env('KAFKA_SASL_PASSWORD', ''),
        ],

        'database' => [
            'table' => 'broker_messages',
        ],
    ],

    'retry' => [
        'max_attempts'     => (int) env('BROKER_RETRY_MAX', 3),
        'initial_delay_ms' => (int) env('BROKER_RETRY_DELAY_MS', 500),
        'multiplier'       => 2,
    ],
];
