<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class KeycloakService
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => config('keycloak.server_url'),
            'timeout'  => 10,
        ]);
    }

    public function validateToken(string $token): object
    {
        $publicKeys = $this->getPublicKeys();

        try {
            // JWT::decode accepts array<string, Key> directly in firebase/php-jwt v7
            $decoded = JWT::decode($token, $publicKeys);
            $this->validateClaims($decoded);
            return $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Token validation failed: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, Key>
     */
    public function getPublicKeys(): array
    {
        return Cache::remember('keycloak_public_keys', 3600, function () {
            $response = $this->httpClient->get(
                config('keycloak.realm_url') . '/protocol/openid-connect/certs'
            );
            $jwks = json_decode($response->getBody()->getContents(), true);
            return JWK::parseKeySet($jwks);
        });
    }

    private function validateClaims(object $decoded): void
    {
        if ($decoded->exp < time()) {
            throw new \Exception('Token has expired');
        }

        if ($decoded->iss !== config('keycloak.realm_url')) {
            throw new \Exception('Invalid token issuer');
        }
    }

    public function createUser(array $userData, string $adminToken): array
    {
        $response = $this->httpClient->post(
            config('keycloak.admin_url') . '/users',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'username'  => $userData['username'],
                    'email'     => $userData['email'],
                    'firstName' => $userData['first_name'] ?? '',
                    'lastName'  => $userData['last_name'] ?? '',
                    'enabled'   => true,
                    'attributes' => [
                        'tenant_id' => [$userData['tenant_id'] ?? ''],
                    ],
                    'credentials' => [
                        [
                            'type'      => 'password',
                            'value'     => $userData['password'],
                            'temporary' => false,
                        ],
                    ],
                ],
            ]
        );

        return ['status' => $response->getStatusCode()];
    }

    public function updateUser(string $keycloakId, array $userData, string $adminToken): void
    {
        $this->httpClient->put(
            config('keycloak.admin_url') . "/users/{$keycloakId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'firstName'  => $userData['first_name'] ?? '',
                    'lastName'   => $userData['last_name'] ?? '',
                    'email'      => $userData['email'] ?? '',
                    'attributes' => [
                        'tenant_id' => [$userData['tenant_id'] ?? ''],
                    ],
                ],
            ]
        );
    }

    public function deleteUser(string $keycloakId, string $adminToken): void
    {
        $this->httpClient->delete(
            config('keycloak.admin_url') . "/users/{$keycloakId}",
            [
                'headers' => ['Authorization' => 'Bearer ' . $adminToken],
            ]
        );
    }

    public function assignRole(string $userId, string $role, string $adminToken): void
    {
        $this->httpClient->post(
            config('keycloak.admin_url') . "/users/{$userId}/role-mappings/realm",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $adminToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    ['name' => $role],
                ],
            ]
        );
    }

    public function getAdminToken(): string
    {
        $response = $this->httpClient->post(
            config('keycloak.server_url') . '/realms/master/protocol/openid-connect/token',
            [
                'form_params' => [
                    'client_id'  => 'admin-cli',
                    'username'   => config('keycloak.admin_username'),
                    'password'   => config('keycloak.admin_password'),
                    'grant_type' => 'password',
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'];
    }
}
