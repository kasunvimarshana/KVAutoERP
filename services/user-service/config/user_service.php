<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Service Identity
    |--------------------------------------------------------------------------
    */
    'name'    => env('APP_NAME', 'User Service'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Service-to-Service API Key
    |--------------------------------------------------------------------------
    | Shared secret used by other microservices to authenticate internal
    | calls (e.g. Auth Service fetching user claims for JWT enrichment).
    */
    'service_key' => env('USER_SERVICE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | JWT Public Key — used for local token verification only.
    | The user service never signs tokens; it only verifies them.
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'public_key'     => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/public.pem')),
        'algorithm'      => env('JWT_ALGORITHM', 'RS256'),
        'leeway_seconds' => (int) env('JWT_LEEWAY_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis revocation list configuration.
    |--------------------------------------------------------------------------
    */
    'revocation' => [
        'jti_prefix'     => env('REVOCATION_JTI_PREFIX', 'revoke:jti:'),
        'version_prefix' => env('REVOCATION_VERSION_PREFIX', 'revoke:user:'),
        'device_prefix'  => env('REVOCATION_DEVICE_PREFIX', 'revoke:device:'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination defaults
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 20),
    ],

];
