<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Public Key
    |--------------------------------------------------------------------------
    |
    | The public key used to verify JWT signatures issued by the Auth service.
    | Provide ONE of the following (in order of precedence):
    |
    | 1. SHARED_AUTH_JWT_PUBLIC_KEY – PEM-encoded key as environment variable
    | 2. SHARED_AUTH_JWT_PUBLIC_KEY_PATH – path to a .pem file
    | 3. SHARED_AUTH_AUTH_SERVICE_JWKS_URL – URL to fetch the key (cached 1 hour)
    |
    */

    'jwt_public_key'        => env('SHARED_AUTH_JWT_PUBLIC_KEY'),
    'jwt_public_key_path'   => env('SHARED_AUTH_JWT_PUBLIC_KEY_PATH', 'storage/keys/auth-public.pem'),
    'auth_service_jwks_url' => env('SHARED_AUTH_AUTH_SERVICE_JWKS_URL'),

    /*
    |--------------------------------------------------------------------------
    | JWT Algorithm
    |--------------------------------------------------------------------------
    */

    'jwt_algo' => env('SHARED_AUTH_JWT_ALGO', 'RS256'),

    /*
    |--------------------------------------------------------------------------
    | Token Revocation (Redis)
    |--------------------------------------------------------------------------
    |
    | Redis key prefix for the revocation list synced from the Auth service.
    | This must match the prefix used by the Auth service.
    |
    */

    'revocation_prefix' => env('SHARED_AUTH_REVOCATION_PREFIX', 'kv_auth_revoked:'),

    /*
    |--------------------------------------------------------------------------
    | Issuer Validation
    |--------------------------------------------------------------------------
    */

    'expected_issuer' => env('SHARED_AUTH_EXPECTED_ISSUER', env('APP_URL')),

];
