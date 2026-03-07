<?php

return [
    'inventory_service_url'     => env('INVENTORY_SERVICE_URL',     'http://inventory-service:8003'),
    'auth_service_url'          => env('AUTH_SERVICE_URL',          'http://auth-service:8001'),
    'tenant_service_url'        => env('TENANT_SERVICE_URL',        'http://tenant-service:8002'),
    'notification_service_url'  => env('NOTIFICATION_SERVICE_URL',  'http://notification-service:8005'),
    'payment_gateway_url'       => env('PAYMENT_GATEWAY_URL',       'http://payment-gateway:8006'),
];
