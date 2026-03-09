<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Naming
    |--------------------------------------------------------------------------
    |
    | Controls how tenant database names are derived from the tenant slug.
    | The final name is: {database_prefix}{slug}{database_suffix}
    |
    */
    'database_prefix' => env('TENANT_DB_PREFIX', 'tenant_'),
    'database_suffix' => env('TENANT_DB_SUFFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Tenant Model & Column
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used to resolve tenants and the column used to scope
    | tenant-owned records throughout the application.
    |
    */
    'tenant_model'  => \App\Infrastructure\Persistence\Models\Tenant::class,
    'tenant_column' => 'tenant_id',

    /*
    |--------------------------------------------------------------------------
    | Provisioning Options
    |--------------------------------------------------------------------------
    |
    | auto_provision: When true, a new isolated database is created immediately
    |   upon tenant creation. Set to false to defer provisioning to a queue job.
    |
    | shared_database: When true, all tenants share a single database and are
    |   isolated by the tenant_id column only (no per-tenant DB creation).
    |   Overrides auto_provision.
    |
    */
    'auto_provision'  => env('TENANT_AUTO_PROVISION', true),
    'shared_database' => env('TENANT_SHARED_DATABASE', false),

    /*
    |--------------------------------------------------------------------------
    | Runtime Config Drivers
    |--------------------------------------------------------------------------
    |
    | Lists the drivers consulted (in order) when loading runtime configuration
    | for the active tenant.
    |
    | Supported: "db", "redis", "file"
    |
    */
    'runtime_config_drivers' => [
        env('TENANT_CONFIG_DRIVER', 'db'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    |
    | Defines the available subscription plans and their capabilities.
    |
    */
    'plans' => [
        'starter' => [
            'max_users'    => 5,
            'max_products' => 500,
            'features'     => ['inventory'],
        ],
        'pro' => [
            'max_users'    => 25,
            'max_products' => 5000,
            'features'     => ['inventory', 'reports', 'api_access'],
        ],
        'enterprise' => [
            'max_users'    => -1,  // unlimited
            'max_products' => -1,
            'features'     => ['inventory', 'reports', 'api_access', 'sso', 'custom_domain'],
        ],
    ],

];
