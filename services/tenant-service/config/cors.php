<?php

declare(strict_types=1);

return [

    'paths'                    => ['api/*', 'sanctum/csrf-cookie', 'health', 'health/*'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => false,

];
