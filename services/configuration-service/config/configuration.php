<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration Cache TTL
    |--------------------------------------------------------------------------
    | How long (in seconds) to cache tenant configuration values in Redis.
    | Set to 0 to disable caching.
    */
    'config_cache_ttl' => (int) env('CONFIG_CACHE_TTL_SECONDS', 300),

    /*
    |--------------------------------------------------------------------------
    | Feature Flag Cache TTL
    |--------------------------------------------------------------------------
    | How long (in seconds) to cache feature flag states in Redis.
    | Lower values mean faster rollout propagation but more DB reads.
    */
    'feature_flag_cache_ttl' => (int) env('FEATURE_FLAG_CACHE_TTL_SECONDS', 60),

    /*
    |--------------------------------------------------------------------------
    | Tenant Isolation Mode
    |--------------------------------------------------------------------------
    | strict: All queries are scoped to tenant_id (enforced at service layer).
    */
    'tenant_isolation_mode' => env('TENANT_ISOLATION_MODE', 'strict'),

];
