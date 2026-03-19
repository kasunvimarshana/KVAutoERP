<?php

declare(strict_types=1);

/**
 * JWT configuration for the User Service.
 *
 * This service only verifies tokens issued by the central Auth Service;
 * it never issues tokens and therefore does not need the private key.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Signing Algorithm
    |--------------------------------------------------------------------------
    | Supported: RS256, RS384, RS512 (RSA asymmetric). Default is RS256.
    */
    'algorithm' => env('JWT_ALGORITHM', 'RS256'),

    /*
    |--------------------------------------------------------------------------
    | RSA Public Key Path
    |--------------------------------------------------------------------------
    | Absolute path to the Auth Service's RSA public key PEM file.
    | Used for local signature verification — no Auth Service call needed.
    */
    'public_key' => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/public.pem')),

    /*
    |--------------------------------------------------------------------------
    | Access Token TTL (seconds)
    |--------------------------------------------------------------------------
    | Expected lifetime of an access token, used for LooseValidAt leeway.
    */
    'access_token_ttl' => (int) env('JWT_ACCESS_TOKEN_TTL', 900),

    /*
    |--------------------------------------------------------------------------
    | Clock Leeway (seconds)
    |--------------------------------------------------------------------------
    | Tolerance for clock skew between services when validating expiry claims.
    */
    'leeway_seconds' => (int) env('JWT_LEEWAY_SECONDS', 30),

    /*
    |--------------------------------------------------------------------------
    | Token Issuer
    |--------------------------------------------------------------------------
    | The expected `iss` claim value. Tokens with a different issuer are
    | rejected. Must match the Auth Service issuer configuration.
    */
    'issuer' => env('JWT_ISSUER', 'kv-enterprise-auth'),

];
