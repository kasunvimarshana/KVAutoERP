<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Header
    |--------------------------------------------------------------------------
    | The HTTP request header that carries the tenant identifier.
    */
    'header' => env('TENANT_HEADER', 'X-Tenant-ID'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Service URL
    |--------------------------------------------------------------------------
    | Base URL of the tenant-service used to validate tenant IDs.
    */
    'service_url' => env('TENANT_SERVICE_URL', 'http://tenant-service:8002'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    | How many seconds a resolved tenant record is cached in Redis.
    | Set to 0 to disable caching.
    */
    'cache_ttl' => (int) env('TENANT_CACHE_TTL', 3600),
];
