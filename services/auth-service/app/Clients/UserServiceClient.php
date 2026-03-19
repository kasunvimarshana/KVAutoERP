<?php

declare(strict_types=1);

namespace App\Clients;

use App\Contracts\Services\UserProviderInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for the User Service internal API.
 *
 * Calls the User Service's `/api/internal/v1/users/{authUserId}/claims`
 * endpoint to retrieve enriched JWT claims (roles, permissions, tenant
 * hierarchy) for a given auth user. Authenticates requests using the
 * shared `X-Service-Key` header.
 *
 * This client implements the UserProviderInterface so the Auth Service
 * never directly depends on the HTTP transport — the dependency can be
 * swapped or mocked without touching any other code.
 */
final class UserServiceClient implements UserProviderInterface
{
    private readonly string $baseUrl;
    private readonly string $serviceKey;
    private readonly int $timeoutSeconds;

    public function __construct(
        private readonly HttpFactory $http,
    ) {
        $this->baseUrl        = rtrim((string) config('auth_service.user_service.base_url', ''), '/');
        $this->serviceKey     = (string) config('auth_service.user_service.service_key', '');
        $this->timeoutSeconds = (int)    config('auth_service.user_service.timeout_seconds', 5);
    }

    /**
     * {@inheritDoc}
     *
     * Calls `GET /api/internal/v1/users/{authUserId}/claims?tenant_id={tenantId}`
     * on the User Service and returns the parsed claims array.
     *
     * Returns null when:
     *   - The base URL is not configured (User Service integration disabled).
     *   - The User Service returns a non-2xx response.
     *   - A network error or timeout occurs (fail-open: Auth Service remains available).
     *
     * @param  string  $authUserId
     * @param  string  $tenantId
     * @return array<string, mixed>|null
     */
    public function getClaimsForUser(string $authUserId, string $tenantId): ?array
    {
        if ($this->baseUrl === '') {
            return null;
        }

        try {
            $response = $this->http
                ->timeout($this->timeoutSeconds)
                ->withHeaders([
                    'X-Service-Key' => $this->serviceKey,
                    'Accept'        => 'application/json',
                ])
                ->get("{$this->baseUrl}/api/internal/v1/users/{$authUserId}/claims", [
                    'tenant_id' => $tenantId,
                ]);

            if (!$response->successful()) {
                Log::warning('UserServiceClient: failed to retrieve claims', [
                    'auth_user_id' => $authUserId,
                    'tenant_id'    => $tenantId,
                    'status'       => $response->status(),
                ]);

                return null;
            }

            /** @var array<string, mixed> $body */
            $body = $response->json();

            return $body['data'] ?? null;
        } catch (\Throwable $e) {
            // Fail open — a User Service outage must not prevent login.
            Log::error('UserServiceClient: exception while fetching claims', [
                'auth_user_id' => $authUserId,
                'tenant_id'    => $tenantId,
                'error'        => $e->getMessage(),
            ]);

            return null;
        }
    }
}
