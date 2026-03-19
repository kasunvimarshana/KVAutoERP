<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Configuration (public key only — no private key needed here)
    |--------------------------------------------------------------------------
    | The User service only verifies tokens issued by the Auth service.
    | It never issues tokens itself.
    */
    'public_key_path' => env('JWT_PUBLIC_KEY_PATH', ''),
    'public_key'      => env('JWT_PUBLIC_KEY', ''),
    'issuer'          => env('JWT_ISSUER', 'kv-saas-auth'),
    'algorithm'       => env('JWT_ALGORITHM', 'RS256'),
];
