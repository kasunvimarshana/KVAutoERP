<?php

declare(strict_types=1);

return [
    'id' => env('TENANT_ID', 'default'),
    'header' => env('TENANT_HEADER', 'X-Tenant-ID'),
    'cache_ttl' => (int) env('TENANT_CACHE_TTL', 300),
];
