<?php

declare(strict_types=1);

namespace App\Http\Clients;

use App\Contracts\UserServiceClientContract;
use App\DTOs\UserDto;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client that communicates with the User microservice.
 * Auth interacts with User exclusively through this adapter,
 * maintaining strict service isolation.
 */
class UserServiceClient implements UserServiceClientContract
{
    private readonly Client $http;

    public function __construct()
    {
        $baseUrl      = (string) config('services.user_service.url', 'http://user-service:8000');
        $serviceToken = (string) config('services.user_service.token', '');

        $this->http = new Client([
            'base_uri' => $baseUrl,
            'timeout'  => 5.0,
            'headers'  => [
                'Authorization' => 'Bearer ' . $serviceToken,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'X-Service-Id'  => 'auth-service',
            ],
        ]);
    }

    public function findById(string $userId): ?UserDto
    {
        return Cache::remember("usr:{$userId}", 60, function () use ($userId) {
            try {
                $response = $this->http->get("/api/v1/internal/users/{$userId}");
                $body     = (array) json_decode((string) $response->getBody(), true);

                return UserDto::fromArray($body['data']);
            } catch (GuzzleException $e) {
                Log::error('UserService.findById failed', ['user_id' => $userId, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }

    public function findByEmail(string $email): ?UserDto
    {
        try {
            $response = $this->http->get('/api/v1/internal/users/by-email', [
                'query' => ['email' => $email],
            ]);

            $body = (array) json_decode((string) $response->getBody(), true);

            return UserDto::fromArray($body['data']);
        } catch (GuzzleException $e) {
            Log::error('UserService.findByEmail failed', ['email' => $email, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function findByExternalId(string $externalId, string $provider): ?UserDto
    {
        try {
            $response = $this->http->get('/api/v1/internal/users/by-external-id', [
                'query' => ['external_id' => $externalId, 'provider' => $provider],
            ]);

            $body = (array) json_decode((string) $response->getBody(), true);

            return UserDto::fromArray($body['data']);
        } catch (GuzzleException) {
            return null;
        }
    }

    public function validateCredentials(string $userId, string $password): bool
    {
        try {
            $response = $this->http->post('/api/v1/internal/users/validate-credentials', [
                'json' => ['user_id' => $userId, 'password' => $password],
            ]);

            $body = (array) json_decode((string) $response->getBody(), true);

            return (bool) ($body['data']['valid'] ?? false);
        } catch (GuzzleException $e) {
            Log::error('UserService.validateCredentials failed', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getUserClaims(string $userId): array
    {
        try {
            $response = $this->http->get("/api/v1/internal/users/{$userId}/claims");
            $body     = (array) json_decode((string) $response->getBody(), true);

            return (array) ($body['data'] ?? []);
        } catch (GuzzleException) {
            return [];
        }
    }

    public function recordLoginEvent(string $userId, string $deviceId, string $ipAddress): void
    {
        try {
            $this->http->post('/api/v1/internal/users/login-event', [
                'json' => [
                    'user_id'    => $userId,
                    'device_id'  => $deviceId,
                    'ip_address' => $ipAddress,
                ],
            ]);
        } catch (GuzzleException $e) {
            Log::warning('UserService.recordLoginEvent failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }

    public function incrementTokenVersion(string $userId): int
    {
        try {
            $response = $this->http->post("/api/v1/internal/users/{$userId}/increment-token-version");
            $body     = (array) json_decode((string) $response->getBody(), true);

            return (int) ($body['data']['token_version'] ?? 1);
        } catch (GuzzleException) {
            return 1;
        }
    }

    /**
     * Fetch the tenant's runtime IAM provider name and configuration.
     *
     * This enables the Auth service to dynamically resolve the correct IAM
     * adapter per tenant at login time without relying on static env vars.
     * Results are cached for 60 seconds so security-sensitive changes
     * (e.g. disabling a provider, rotating credentials) propagate promptly.
     *
     * @return array{iam_provider: string, iam_config: array<string, mixed>, status: string}
     */
    public function getTenantIamConfig(string $tenantId): array
    {
        return Cache::remember("tenant_iam:{$tenantId}", 60, function () use ($tenantId): array {
            try {
                $response = $this->http->get("/api/v1/internal/tenants/{$tenantId}/iam-config");
                $body     = (array) json_decode((string) $response->getBody(), true);

                return (array) ($body['data'] ?? []);
            } catch (GuzzleException $e) {
                Log::error('UserService.getTenantIamConfig failed', ['tenant_id' => $tenantId, 'error' => $e->getMessage()]);

                return ['iam_provider' => 'local', 'iam_config' => [], 'status' => 'active'];
            }
        });
    }
}
