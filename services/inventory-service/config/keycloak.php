<?php

return [
    'base_url'    => env('KEYCLOAK_BASE_URL', 'http://localhost:8080'),
    'realm'       => env('KEYCLOAK_REALM', 'inventory-realm'),
    'client_id'   => env('KEYCLOAK_CLIENT_ID', 'product-service'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', ''),
    'admin_url'   => env('KEYCLOAK_BASE_URL', 'http://localhost:8080')
                     . '/admin/realms/' . env('KEYCLOAK_REALM', 'inventory-realm'),
    'admin_client_id'     => env('KEYCLOAK_ADMIN_CLIENT_ID', 'admin-cli'),
    'admin_client_secret' => env('KEYCLOAK_ADMIN_CLIENT_SECRET', ''),
    'public_key'  => env('KEYCLOAK_PUBLIC_KEY', ''),
];
