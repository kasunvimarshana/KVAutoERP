<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    */
    'guard' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Token Expiry (in minutes/days)
    |--------------------------------------------------------------------------
    */

    /** Access token lifetime in minutes. */
    'token_expiry' => (int) env('PASSPORT_TOKEN_EXPIRY', 60),

    /** Refresh token lifetime in days. */
    'refresh_token_expiry' => (int) env('PASSPORT_REFRESH_TOKEN_EXPIRY', 30),

    /** Personal access token lifetime in months. */
    'personal_access_token_expiry' => (int) env('PASSPORT_PAT_EXPIRY', 6),

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    | Keys are loaded from the filesystem paths below.  In production these
    | should be mounted as secrets (e.g. Kubernetes Secrets).
    */
    'private_key' => env('PASSPORT_PRIVATE_KEY', storage_path('oauth-private.key')),
    'public_key'  => env('PASSPORT_PUBLIC_KEY', storage_path('oauth-public.key')),

    /*
    |--------------------------------------------------------------------------
    | Client UUIDs
    |--------------------------------------------------------------------------
    */
    'use_uuids' => true,

    /*
    |--------------------------------------------------------------------------
    | Hash Client Secrets
    |--------------------------------------------------------------------------
    */
    'hash_client_secrets' => true,

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
