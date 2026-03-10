<?php

declare(strict_types=1);

return [
    'auth' => [
        'url' => env('AUTH_SERVICE_URL', 'http://auth-service:8001'),
        'introspect_url' => env('PASSPORT_INTROSPECT_URL', 'http://auth-service:8001/api/auth/introspect'),
    ],
];
