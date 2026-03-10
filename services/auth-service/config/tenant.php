<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Tenant ID
    |--------------------------------------------------------------------------
    | The default tenant ID when none is specified.
    | Used as a fallback in single-tenant deployments.
    */
    'id' => env('TENANT_ID', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution
    |--------------------------------------------------------------------------
    | How to resolve the tenant from a request.
    | Options: header, domain, path, query
    */
    'resolver' => env('TENANT_RESOLVER', 'header'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Header Name
    |--------------------------------------------------------------------------
    */
    'header' => env('TENANT_HEADER', 'X-Tenant-ID'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    | How long to cache tenant configurations (seconds).
    */
    'cache_ttl' => (int) env('TENANT_CACHE_TTL', 300),
];
