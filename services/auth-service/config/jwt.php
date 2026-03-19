<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    */
    'private_key_path' => env('JWT_PRIVATE_KEY_PATH', storage_path('keys/private.pem')),
    'public_key_path'  => env('JWT_PUBLIC_KEY_PATH',  storage_path('keys/public.pem')),
    'issuer'           => env('JWT_ISSUER',      'kv-saas-auth'),
    'ttl'              => (int) env('JWT_TTL',          900),        // 15 minutes
    'refresh_ttl'      => (int) env('JWT_REFRESH_TTL',  2592000),    // 30 days
    'service_ttl'      => (int) env('JWT_SERVICE_TTL',  3600),       // 1 hour
    'algorithm'        => env('JWT_ALGORITHM',   'RS256'),
];
