<?php

return [
    'service_token'        => env('SERVICE_TOKEN'),
    'webhook_secret'       => env('WEBHOOK_SECRET'),
    'inventory_service_url' => env('INVENTORY_SERVICE_URL', 'http://inventory-service:8003'),
    'auth_service_url'     => env('AUTH_SERVICE_URL', 'http://auth-service:8000'),
];
