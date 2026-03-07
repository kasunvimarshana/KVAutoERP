<?php

return [
    'service_token'       => env('SERVICE_TOKEN', ''),
    'product_service_url' => env('PRODUCT_SERVICE_URL', 'http://product-service:8002'),
    'auth_service_url'    => env('AUTH_SERVICE_URL', 'http://auth-service:8000'),
];
