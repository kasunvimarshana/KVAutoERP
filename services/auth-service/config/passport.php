<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Token Lifetimes
    |--------------------------------------------------------------------------
    |
    | All values are in MINUTES unless otherwise noted.
    |
    */

    // Access token lifetime in minutes (default: 24 hours)
    'token_lifetime' => (int) env('PASSPORT_TOKEN_LIFETIME', 1440),

    // Refresh token lifetime in days
    'refresh_token_lifetime' => (int) env('PASSPORT_REFRESH_TOKEN_LIFETIME', 30),

    // Personal access token lifetime in months
    'personal_access_token_lifetime' => (int) env('PASSPORT_PERSONAL_ACCESS_LIFETIME', 6),

    /*
    |--------------------------------------------------------------------------
    | Passport Storage
    |--------------------------------------------------------------------------
    */

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Passport Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys to generate secure access tokens.
    | Use `php artisan passport:keys` to generate these files.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY', storage_path('oauth-private.key')),
    'public_key'  => env('PASSPORT_PUBLIC_KEY', storage_path('oauth-public.key')),

    /*
    |--------------------------------------------------------------------------
    | Allowed Clients
    |--------------------------------------------------------------------------
    |
    | Restrict which client IDs are allowed to issue tokens.
    | Leave empty to allow all registered clients.
    |
    */

    'allowed_clients' => [],

    /*
    |--------------------------------------------------------------------------
    | Hash Client Secrets
    |--------------------------------------------------------------------------
    */

    'hash_client_secrets' => (bool) env('PASSPORT_HASH_CLIENT_SECRETS', true),

];
