<?php

use Laravel\Passport\Passport;

return [
    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    */
    'guard' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    | Loaded from env or storage/oauth-private.key & storage/oauth-public.key
    */
    'private_key' => env('PASSPORT_PRIVATE_KEY'),
    'public_key'  => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Client UUIDs
    |--------------------------------------------------------------------------
    */
    'client_uuids' => true,

    /*
    |--------------------------------------------------------------------------
    | Token TTLs (in minutes)
    |--------------------------------------------------------------------------
    */
    'token_expire_in'         => env('PASSPORT_TOKEN_EXPIRE_IN',         60),
    'refresh_token_expire_in' => env('PASSPORT_REFRESH_TOKEN_EXPIRE_IN', 20160),
    'personal_access_token_expire_in' => env('PASSPORT_PERSONAL_ACCESS_TOKEN_EXPIRE_IN', 525600),

    /*
    |--------------------------------------------------------------------------
    | Personal Access Client
    |--------------------------------------------------------------------------
    */
    'personal_access_client' => [
        'id'     => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
        'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
    ],
];
