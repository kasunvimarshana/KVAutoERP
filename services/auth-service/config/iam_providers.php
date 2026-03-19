<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IAM Provider Configurations
    | Resolved at runtime per tenant — no redeployment needed.
    |--------------------------------------------------------------------------
    */

    /*
    | Local (username + password) provider.
    | Credential validation is delegated to the User microservice.
    | No external OAuth2/OIDC endpoints are required.
    */
    'local' => [
        'password_min_length' => (int) env('LOCAL_AUTH_PASSWORD_MIN_LENGTH', 8),
        'max_devices'         => (int) env('LOCAL_AUTH_MAX_DEVICES', 10),
    ],

    'okta' => [
        'domain'        => env('OKTA_DOMAIN', ''),
        'client_id'     => env('OKTA_CLIENT_ID', ''),
        'client_secret' => env('OKTA_CLIENT_SECRET', ''),
        'authorization_endpoint' => env('OKTA_AUTH_ENDPOINT', ''),
        'scope'         => env('OKTA_SCOPE', 'openid profile email'),
    ],

    'keycloak' => [
        'base_url'      => env('KEYCLOAK_BASE_URL', 'http://keycloak:8080'),
        'realm'         => env('KEYCLOAK_REALM', 'master'),
        'client_id'     => env('KEYCLOAK_CLIENT_ID', ''),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET', ''),
        'authorization_endpoint' => env('KEYCLOAK_AUTH_ENDPOINT', ''),
    ],

    'azure_ad' => [
        'azure_tenant_id' => env('AZURE_AD_TENANT_ID', 'common'),
        'client_id'       => env('AZURE_AD_CLIENT_ID', ''),
        'client_secret'   => env('AZURE_AD_CLIENT_SECRET', ''),
        'authorization_endpoint' => env('AZURE_AD_AUTH_ENDPOINT', ''),
    ],

    'oauth2' => [
        'client_id'           => env('OAUTH2_CLIENT_ID', ''),
        'client_secret'       => env('OAUTH2_CLIENT_SECRET', ''),
        'token_endpoint'      => env('OAUTH2_TOKEN_ENDPOINT', ''),
        'userinfo_endpoint'   => env('OAUTH2_USERINFO_ENDPOINT', ''),
        'authorization_endpoint' => env('OAUTH2_AUTH_ENDPOINT', ''),
        'scope'               => env('OAUTH2_SCOPE', 'openid profile email'),
        'provider_name'       => env('OAUTH2_PROVIDER_NAME', 'oauth2'),
    ],

    'saml' => [
        'entity_id'       => env('SAML_ENTITY_ID', ''),
        'sso_url'         => env('SAML_SSO_URL', ''),
        'slo_url'         => env('SAML_SLO_URL', ''),
        'idp_certificate' => env('SAML_IDP_CERTIFICATE', ''),
        'sp_private_key'  => env('SAML_SP_PRIVATE_KEY', ''),
        'sp_certificate'  => env('SAML_SP_CERTIFICATE', ''),
        'name_id_format'  => env('SAML_NAME_ID_FORMAT', 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'),
    ],
];
