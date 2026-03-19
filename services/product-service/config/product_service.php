<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Service Identity
    |--------------------------------------------------------------------------
    */
    'name'    => env('APP_NAME', 'Product Service'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | JWT Public Key (RSA) — used for local token verification only.
    | The product service never signs tokens; it only verifies them.
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'public_key'    => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/oauth-public.key')),
        'algorithm'     => env('JWT_ALGORITHM', 'RS256'),
        'leeway_seconds' => (int) env('JWT_LEEWAY_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis revocation list configuration.
    | Keys used: jti:<jti> and token_version:<user_id>
    |--------------------------------------------------------------------------
    */
    'revocation' => [
        'jti_prefix'           => env('JWT_REVOCATION_JTI_PREFIX', 'jti:'),
        'version_prefix'       => env('JWT_REVOCATION_VERSION_PREFIX', 'token_version:'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Service base URL (for service-to-service calls, health checks).
    |--------------------------------------------------------------------------
    */
    'auth_service_url' => env('AUTH_SERVICE_URL', 'http://auth-service:8001'),

    /*
    |--------------------------------------------------------------------------
    | Pagination defaults
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => (int) env('DEFAULT_PER_PAGE', 15),
        'max_per_page'     => (int) env('MAX_PER_PAGE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Product domain defaults
    |--------------------------------------------------------------------------
    */
    'product' => [
        'default_cost_method' => env('DEFAULT_COST_METHOD', 'weighted_average'),
        'image_disk'          => env('PRODUCT_IMAGE_DISK', 'public'),
        'image_max_size_kb'   => (int) env('PRODUCT_IMAGE_MAX_SIZE_KB', 5120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Financial precision settings
    |--------------------------------------------------------------------------
    */
    'finance' => [
        'price_decimal_places'          => 4,
        'uom_conversion_decimal_places' => 6,
        'rounding_mode'                 => PHP_ROUND_HALF_UP,
    ],

];
