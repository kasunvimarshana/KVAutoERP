<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Mode
    |--------------------------------------------------------------------------
    |
    | 'single'   – All tenants share one database; tenant data is scoped by
    |              a tenant_id column on every table (default).
    |
    | 'multi'    – Each tenant has a dedicated database. Requires the
    |              database_name field on the tenants table.
    |
    */

    'mode' => env('TENANCY_MODE', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution
    |--------------------------------------------------------------------------
    |
    | The ordered list of strategies used to resolve the current tenant.
    | They are tried in order until one succeeds.
    |
    */

    'resolution_strategies' => [
        'header',       // X-Tenant-ID or X-Tenant-Slug
        'subdomain',    // acme.app.example.com → slug = acme
        'query',        // ?tenant_id=... or ?tenant_slug=...
        'jwt_claim',    // "tenant_id" claim inside a JWT
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) resolved tenants are cached to avoid repeated
    | database lookups on every request.
    |
    */

    'cache_ttl' => (int) env('TENANT_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Subdomain Extraction
    |--------------------------------------------------------------------------
    |
    | When using subdomain-based tenant resolution, specify how many domain
    | parts from the right represent the root domain.
    |
    | e.g. for "acme.app.example.com" with root_parts=2:
    |   root domain = example.com
    |   app prefix  = app
    |   tenant slug = acme
    |
    */

    'subdomain' => [
        'root_domain' => env('TENANT_ROOT_DOMAIN', 'example.com'),
        'root_parts'  => (int) env('TENANT_ROOT_DOMAIN_PARTS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles for New Tenant Users
    |--------------------------------------------------------------------------
    */

    'default_user_roles' => ['user'],

    /*
    |--------------------------------------------------------------------------
    | Tenant-Scoped Tables
    |--------------------------------------------------------------------------
    |
    | Tables that carry a tenant_id column. Used in global scope application.
    |
    */

    'scoped_tables' => [
        'users',
        'organizations',
        'oauth_access_tokens',
        'oauth_auth_codes',
        'oauth_clients',
        'oauth_refresh_tokens',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'default_max_users' => (int) env('TENANT_DEFAULT_MAX_USERS', 100),
    ],

];
