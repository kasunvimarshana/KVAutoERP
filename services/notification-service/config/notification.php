<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'retry_attempts' => (int) env('WEBHOOK_RETRY_ATTEMPTS', 3),
        'retry_backoff'  => (int) env('WEBHOOK_RETRY_BACKOFF', 60), // seconds
        'timeout'        => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Event Types
    |--------------------------------------------------------------------------
    */
    'events' => [
        'inventory.low',
        'inventory.updated',
        'order.created',
        'order.completed',
        'order.failed',
        'order.cancelled',
        'tenant.activated',
        'tenant.suspended',
        'user.registered',
        'webhook.test',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */
    'channels' => ['email', 'slack', 'webhook', 'push'],
];
