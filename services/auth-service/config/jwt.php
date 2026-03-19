<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Signing Algorithm
    |--------------------------------------------------------------------------
    | Supported: RS256 (default), RS384, RS512
    | Cryptographic agility: change algorithm at runtime via config without
    | architectural changes. The algorithm is embedded in the JWT header so
    | existing tokens remain verifiable with the same key pair.
    */

    'algorithm' => env('JWT_ALGORITHM', 'RS256'),

    /*
    |--------------------------------------------------------------------------
    | Token Lifetimes (in seconds)
    |--------------------------------------------------------------------------
    */

    'access_token_ttl'  => (int) env('JWT_ACCESS_TOKEN_TTL', 900),    // 15 minutes
    'refresh_token_ttl' => (int) env('JWT_REFRESH_TOKEN_TTL', 604800), // 7 days

    /*
    |--------------------------------------------------------------------------
    | Issuer
    |--------------------------------------------------------------------------
    | The `iss` claim embedded in every token.
    */

    'issuer' => env('JWT_ISSUER', 'https://auth.kv-enterprise.io'),

    /*
    |--------------------------------------------------------------------------
    | RSA Key Paths
    |--------------------------------------------------------------------------
    | Absolute or storage-relative paths to the PEM-encoded RSA key files.
    | Generate with:
    |   openssl genrsa -out storage/keys/jwt-private.pem 4096
    |   openssl rsa -in storage/keys/jwt-private.pem -pubout \
    |       -out storage/keys/jwt-public.pem
    */

    'private_key_path'       => env('JWT_PRIVATE_KEY_PATH', 'storage/keys/jwt-private.pem'),
    'public_key_path'        => env('JWT_PUBLIC_KEY_PATH', 'storage/keys/jwt-public.pem'),
    'private_key_passphrase' => env('JWT_PRIVATE_KEY_PASSPHRASE', ''),

    /*
    |--------------------------------------------------------------------------
    | Algorithm Map
    |--------------------------------------------------------------------------
    | Maps algorithm names to lcobucci/jwt signer classes for cryptographic
    | agility — add RS384/RS512 entries without touching service code.
    */

    'algorithm_map' => [
        'RS256' => \Lcobucci\JWT\Signer\Rsa\Sha256::class,
        'RS384' => \Lcobucci\JWT\Signer\Rsa\Sha384::class,
        'RS512' => \Lcobucci\JWT\Signer\Rsa\Sha512::class,
    ],
];
