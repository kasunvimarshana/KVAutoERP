<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Tenant Plan
    |--------------------------------------------------------------------------
    */
    'default_plan' => env('DEFAULT_TENANT_PLAN', 'free'),

    /*
    |--------------------------------------------------------------------------
    | Trial Period (days)
    |--------------------------------------------------------------------------
    */
    'trial_days' => (int) env('TENANT_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Config Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => (int) env('TENANT_CONFIG_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Plan Limits
    |--------------------------------------------------------------------------
    | -1 means unlimited.
    */
    'plans' => [
        'free' => [
            'max_users'              => 5,
            'max_inventory_items'    => 100,
            'max_orders_per_month'   => 50,
            'max_warehouses'         => 1,
            'features'               => ['basic_inventory', 'basic_orders'],
        ],
        'starter' => [
            'max_users'              => 20,
            'max_inventory_items'    => 1000,
            'max_orders_per_month'   => 500,
            'max_warehouses'         => 3,
            'features'               => ['basic_inventory', 'basic_orders', 'reports', 'webhooks'],
        ],
        'professional' => [
            'max_users'              => 100,
            'max_inventory_items'    => 10000,
            'max_orders_per_month'   => 5000,
            'max_warehouses'         => 10,
            'features'               => ['basic_inventory', 'basic_orders', 'reports', 'webhooks', 'api_access', 'advanced_reports'],
        ],
        'enterprise' => [
            'max_users'              => -1,
            'max_inventory_items'    => -1,
            'max_orders_per_month'   => -1,
            'max_warehouses'         => -1,
            'features'               => ['*'],
        ],
    ],
];
