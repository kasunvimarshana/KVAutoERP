<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Service Identity
    |--------------------------------------------------------------------------
    */
    'name'    => env('APP_NAME', 'Inventory Service'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | JWT Public Key (RSA) — used for local token verification only.
    | The inventory service never signs tokens; it only verifies them.
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'public_key'     => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/oauth-public.key')),
        'algorithm'      => env('JWT_ALGORITHM', 'RS256'),
        'leeway_seconds' => (int) env('JWT_LEEWAY_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis revocation list configuration.
    | Keys used: jti:<jti> and token_version:<user_id>
    |--------------------------------------------------------------------------
    */
    'revocation' => [
        'jti_prefix'     => env('JWT_REVOCATION_JTI_PREFIX', 'jti:'),
        'version_prefix' => env('JWT_REVOCATION_VERSION_PREFIX', 'token_version:'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upstream service URLs (service-to-service calls).
    |--------------------------------------------------------------------------
    */
    'auth_service_url'    => env('AUTH_SERVICE_URL', 'http://auth-service:8001'),
    'product_service_url' => env('PRODUCT_SERVICE_URL', 'http://product-service:8003'),

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
    | Inventory domain defaults
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        'valuation_strategy'  => env('INVENTORY_VALUATION_STRATEGY', 'fifo'),
        'pharma_compliance'   => (bool) env('INVENTORY_PHARMA_COMPLIANCE', false),
        'idempotency_ttl'     => (int) env('IDEMPOTENCY_TTL', 86400),
        'quantity_decimals'   => 4,
        'cost_decimals'       => 4,
        'exchange_rate_decimals' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Financial precision settings
    |--------------------------------------------------------------------------
    */
    'finance' => [
        'cost_decimal_places'    => 4,
        'qty_decimal_places'     => 4,
        'rounding_mode'          => PHP_ROUND_HALF_UP,
    ],

];
