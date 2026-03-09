<?php

declare(strict_types=1);

/**
 * SaaS Platform Configuration
 *
 * All environment-specific values are read from .env to support
 * runtime configuration without code changes.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    */
    'tenancy' => [
        'resolver' => env('TENANT_RESOLVER', 'header'), // header | subdomain | domain
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Broker
    |--------------------------------------------------------------------------
    | Supported: "database" (default), "kafka", "rabbitmq"
    */
    'message_broker' => [
        'driver' => env('MESSAGE_BROKER_DRIVER', 'database'),

        'kafka' => [
            'brokers'  => env('KAFKA_BROKERS', 'localhost:9092'),
            'group_id' => env('KAFKA_GROUP_ID', 'saas-inventory'),
        ],

        'rabbitmq' => [
            'host'     => env('RABBITMQ_HOST', 'localhost'),
            'port'     => (int) env('RABBITMQ_PORT', 5672),
            'user'     => env('RABBITMQ_USER', 'guest'),
            'password' => env('RABBITMQ_PASSWORD', 'guest'),
            'vhost'    => env('RABBITMQ_VHOST', '/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'version'          => env('API_VERSION', 'v1'),
        'default_per_page' => (int) env('API_DEFAULT_PER_PAGE', 15),
        'max_per_page'     => (int) env('API_MAX_PER_PAGE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'timeout_seconds' => (int) env('WEBHOOK_TIMEOUT', 10),
        'retry_attempts'  => (int) env('WEBHOOK_RETRY_ATTEMPTS', 3),
    ],

];
