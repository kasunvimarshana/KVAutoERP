<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Configuration
    |--------------------------------------------------------------------------
    |
    | Controls how tenant isolation is enforced across the platform.
    | The hierarchy is: Tenant → Organisation → Branch → Location → Department
    |
    */

    'isolation_mode' => env('TENANT_ISOLATION_MODE', 'strict'),

    'hierarchy' => [
        'tenant',
        'organisation',
        'branch',
        'location',
        'department',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant-Scoped Resources
    |--------------------------------------------------------------------------
    */

    'scoped_resources' => [
        'queries'        => true,
        'cache'          => true,
        'queues'         => true,
        'storage'        => true,
        'configurations' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | All cached data is namespaced by tenant to prevent cross-tenant leakage.
    |
    */

    'cache_prefix' => 'tenant:{tenant_id}:',

    /*
    |--------------------------------------------------------------------------
    | Default Session Timeout
    |--------------------------------------------------------------------------
    */

    'default_session_timeout_minutes' => (int) env('DEFAULT_SESSION_TIMEOUT_MINUTES', 480),

    /*
    |--------------------------------------------------------------------------
    | Maximum Devices per User
    |--------------------------------------------------------------------------
    */

    'max_devices_per_user' => (int) env('MAX_DEVICES_PER_USER', 10),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags (runtime-configurable per tenant)
    |--------------------------------------------------------------------------
    */

    'default_features' => [
        'sso_enabled'                => true,
        'multi_device_sessions'      => true,
        'suspicious_activity_alerts' => true,
        'audit_logging'              => true,
        'rate_limiting'              => true,
        'signed_urls'                => true,
        'outbox_pattern'             => true,
    ],

];
