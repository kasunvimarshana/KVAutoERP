<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service Registry
    |--------------------------------------------------------------------------
    | Maps service slug => env variable that holds the base URL.
    */
    'services' => [
        'auth'          => env('AUTH_SERVICE_URL',         'http://auth-service:8001'),
        'tenants'       => env('TENANT_SERVICE_URL',       'http://tenant-service:8002'),
        'inventory'     => env('INVENTORY_SERVICE_URL',    'http://inventory-service:8003'),
        'orders'        => env('ORDER_SERVICE_URL',        'http://order-service:8004'),
        'notifications' => env('NOTIFICATION_SERVICE_URL', 'http://notification-service:8005'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy Settings
    |--------------------------------------------------------------------------
    */
    'timeout'         => (int) env('GATEWAY_TIMEOUT', 30),
    'retry_attempts'  => (int) env('GATEWAY_RETRY_ATTEMPTS', 1),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max'            => (int) env('RATE_LIMIT_MAX', 100),
        'decay_minutes'  => (int) env('RATE_LIMIT_DECAY_MINUTES', 1),
    ],
];
