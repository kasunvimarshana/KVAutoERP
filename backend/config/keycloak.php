<?php

return [
    'server_url'     => env('KEYCLOAK_SERVER_URL', 'http://localhost:8080'),
    'realm'          => env('KEYCLOAK_REALM', 'saas-inventory'),
    'realm_url'      => env('KEYCLOAK_REALM_URL', 'http://localhost:8080/realms/saas-inventory'),
    'admin_url'      => env('KEYCLOAK_ADMIN_URL', 'http://localhost:8080/admin/realms/saas-inventory'),
    'client_id'      => env('KEYCLOAK_CLIENT_ID', 'inventory-backend'),
    'client_secret'  => env('KEYCLOAK_CLIENT_SECRET', ''),
    'admin_username' => env('KEYCLOAK_ADMIN_USERNAME', 'admin'),
    'admin_password' => env('KEYCLOAK_ADMIN_PASSWORD', 'admin'),
];
