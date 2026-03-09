<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Mode
    |--------------------------------------------------------------------------
    |
    | 'single' – All tenants share one database; every table carries tenant_id.
    | 'multi'  – Each tenant gets a dedicated database (requires database_config).
    |
    */

    'mode' => env('TENANCY_MODE', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution Strategies
    |--------------------------------------------------------------------------
    |
    | Ordered list of strategies used to resolve the current tenant.
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
    | Tenant Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => (int) env('TENANT_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Subdomain Extraction
    |--------------------------------------------------------------------------
    */

    'subdomain' => [
        'root_domain' => env('TENANT_ROOT_DOMAIN', 'example.com'),
        'root_parts'  => (int) env('TENANT_ROOT_DOMAIN_PARTS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Tenant Limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'max_users'         => (int) env('TENANT_DEFAULT_MAX_USERS', 100),
        'max_organizations' => (int) env('TENANT_DEFAULT_MAX_ORGS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Plan
    |--------------------------------------------------------------------------
    */

    'default_plan' => env('TENANT_DEFAULT_PLAN', 'free'),

    /*
    |--------------------------------------------------------------------------
    | Trial Duration (days)
    |--------------------------------------------------------------------------
    */

    'trial_days' => (int) env('TENANT_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Tenant-Scoped Tables
    |--------------------------------------------------------------------------
    |
    | Tables that carry a tenant_id column, used in global scope application.
    |
    */

    'scoped_tables' => [
        'organizations',
        'webhook_subscriptions',
        'webhook_deliveries',
    ],

    /*
    |--------------------------------------------------------------------------
    | Runtime Config Drivers
    |--------------------------------------------------------------------------
    |
    | Whether to apply runtime database, mail, cache, and broker configs.
    |
    */

    'runtime_config' => [
        'database' => (bool) env('TENANT_RUNTIME_DB', true),
        'mail'     => (bool) env('TENANT_RUNTIME_MAIL', true),
        'cache'    => (bool) env('TENANT_RUNTIME_CACHE', true),
        'broker'   => (bool) env('TENANT_RUNTIME_BROKER', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'default_retry_count' => (int) env('WEBHOOK_DEFAULT_RETRY_COUNT', 3),
        'timeout_seconds'     => (int) env('WEBHOOK_TIMEOUT', 10),
    ],

];
