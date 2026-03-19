<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for JWT token issuance, verification, and rotation for the
    | distributed authentication system.
    |
    */

    'algo' => env('JWT_ALGO', 'RS256'),

    'keys' => [
        'public'  => env('JWT_PUBLIC_KEY_PATH', 'storage/keys/public.pem'),
        'private' => env('JWT_PRIVATE_KEY_PATH', 'storage/keys/private.pem'),
        'secret'  => env('JWT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Lifetimes (in minutes)
    |--------------------------------------------------------------------------
    */

    'ttl' => [
        'access'  => (int) env('JWT_TTL', 15),
        'refresh' => (int) env('JWT_REFRESH_TTL', 43200),
        'service' => (int) env('SERVICE_AUTH_TTL_MINUTES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Refresh Window
    |--------------------------------------------------------------------------
    |
    | Grace window in minutes during which an expired access token may still
    | be used to obtain a new access token via the refresh endpoint.
    |
    */

    'refresh_window_minutes' => (int) env('TOKEN_REFRESH_WINDOW_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Token Claims
    |--------------------------------------------------------------------------
    |
    | Additional claims embedded in every issued token.
    |
    */

    'required_claims' => [
        'iss', 'iat', 'exp', 'nbf', 'sub', 'jti',
    ],

    'custom_claims' => [
        'user_id', 'tenant_id', 'organization_id', 'branch_id', 'location_id',
        'department_id', 'roles', 'permissions', 'device_id', 'token_version',
    ],

    /*
    |--------------------------------------------------------------------------
    | Revocation
    |--------------------------------------------------------------------------
    */

    'revocation' => [
        'driver'    => 'redis',
        'prefix'    => env('REDIS_PREFIX', 'kv_auth_') . 'revoked:',
        'ttl'       => (int) env('JWT_REFRESH_TTL', 43200) * 60, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-to-Service Authentication
    |--------------------------------------------------------------------------
    */

    'service' => [
        'secret' => env('SERVICE_AUTH_SECRET'),
        'header' => 'X-Service-Token',
        'issuer' => env('APP_URL', 'http://localhost:8001'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Version
    |--------------------------------------------------------------------------
    |
    | When true, every password change or global logout increments the
    | user's token_version claim, invalidating all previously issued tokens.
    |
    */

    'version_on_password_change' => env('TOKEN_VERSION_INCREMENT_ON_PASSWORD_CHANGE', true),

    /*
    |--------------------------------------------------------------------------
    | Issuer
    |--------------------------------------------------------------------------
    */

    'issuer' => env('APP_URL', 'http://localhost:8001'),

];
