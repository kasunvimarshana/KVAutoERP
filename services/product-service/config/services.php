<?php

declare(strict_types=1);

return [
    'auth' => [
        'url' => env('AUTH_SERVICE_URL', 'http://auth-service:8001'),
        'introspect_url' => env('PASSPORT_INTROSPECT_URL', 'http://auth-service:8001/api/auth/introspect'),
    ],
    'inventory' => [
        'url' => env('INVENTORY_SERVICE_URL', 'http://inventory-service:8004'),
    ],
    'order' => [
        'url' => env('ORDER_SERVICE_URL', 'http://order-service:8005'),
    ],
];
