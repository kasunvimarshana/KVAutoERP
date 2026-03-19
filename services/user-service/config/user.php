<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Isolation Mode
    |--------------------------------------------------------------------------
    |
    | Controls how strictly tenant isolation is enforced. In 'strict' mode,
    | all queries are scoped to the authenticated tenant and cross-tenant
    | access is blocked at the service layer.
    |
    */
    'isolation_mode' => env('TENANT_ISOLATION_MODE', 'strict'),

    /*
    |--------------------------------------------------------------------------
    | JWT Public Key
    |--------------------------------------------------------------------------
    |
    | Path to the Auth service's public key used to verify JWT signatures.
    | In the simplified VerifyServiceToken middleware, signature verification
    | is delegated to the API gateway.
    |
    */
    'jwt_public_key_path' => env('JWT_PUBLIC_KEY_PATH', 'storage/keys/public.pem'),

];
