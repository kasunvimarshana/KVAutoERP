<?php

declare(strict_types=1);

namespace Tests\Unit\Clients;

use App\Clients\UserServiceClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for UserServiceClient.
 *
 * Verifies that the client correctly calls the User Service internal
 * claims endpoint, handles successful responses, and gracefully handles
 * failure scenarios (4xx, 5xx, network errors).
 */
final class UserServiceClientTest extends TestCase
{
    private const AUTH_USER_ID = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const TENANT_ID    = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    #[Test]
    public function it_returns_claims_from_user_service_on_success(): void
    {
        $expectedClaims = [
            'user_id'         => self::AUTH_USER_ID,
            'tenant_id'       => self::TENANT_ID,
            'organization_id' => 'org-uuid-001',
            'branch_id'       => null,
            'roles'           => ['admin'],
            'permissions'     => ['users.manage'],
            'profile'         => ['first_name' => 'John', 'last_name' => 'Doe'],
        ];

        $http = new HttpFactory();
        $http->fake([
            '*' => HttpFactory::response(
                ['status' => 'success', 'data' => $expectedClaims],
                200,
            ),
        ]);

        config([
            'auth_service.user_service.base_url'        => 'http://user-service',
            'auth_service.user_service.service_key'     => 'secret-key',
            'auth_service.user_service.timeout_seconds' => 5,
        ]);

        $client = new UserServiceClient($http);
        $claims = $client->getClaimsForUser(self::AUTH_USER_ID, self::TENANT_ID);

        $this->assertIsArray($claims);
        $this->assertSame(self::AUTH_USER_ID, $claims['user_id']);
        $this->assertSame(['admin'], $claims['roles']);
        $this->assertSame(['users.manage'], $claims['permissions']);
    }

    #[Test]
    public function it_returns_null_when_base_url_is_not_configured(): void
    {
        $http = new HttpFactory();

        config(['auth_service.user_service.base_url' => '']);

        $client = new UserServiceClient($http);
        $result = $client->getClaimsForUser(self::AUTH_USER_ID, self::TENANT_ID);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_when_user_service_returns_404(): void
    {
        $http = new HttpFactory();
        $http->fake([
            '*' => HttpFactory::response(['status' => 'error', 'message' => 'Not found'], 404),
        ]);

        config([
            'auth_service.user_service.base_url'    => 'http://user-service',
            'auth_service.user_service.service_key' => 'secret-key',
        ]);

        $client = new UserServiceClient($http);
        $result = $client->getClaimsForUser(self::AUTH_USER_ID, self::TENANT_ID);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_when_user_service_returns_500(): void
    {
        $http = new HttpFactory();
        $http->fake([
            '*' => HttpFactory::response(['error' => 'Internal Server Error'], 500),
        ]);

        config([
            'auth_service.user_service.base_url'    => 'http://user-service',
            'auth_service.user_service.service_key' => 'secret-key',
        ]);

        $client = new UserServiceClient($http);
        $result = $client->getClaimsForUser(self::AUTH_USER_ID, self::TENANT_ID);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sends_the_correct_service_key_header(): void
    {
        $http = new HttpFactory();
        $http->fake([
            '*' => HttpFactory::response(
                ['status' => 'success', 'data' => ['user_id' => self::AUTH_USER_ID]],
                200,
            ),
        ]);

        config([
            'auth_service.user_service.base_url'    => 'http://user-service',
            'auth_service.user_service.service_key' => 'my-secret-key',
        ]);

        $client = new UserServiceClient($http);
        $client->getClaimsForUser(self::AUTH_USER_ID, self::TENANT_ID);

        $http->assertSent(static function (Request $request): bool {
            return $request->hasHeader('X-Service-Key', 'my-secret-key')
                && str_contains(
                    (string) $request->url(),
                    '/api/internal/v1/users/' . self::AUTH_USER_ID . '/claims',
                );
        });
    }

    #[Test]
    public function it_sends_tenant_id_as_query_parameter(): void
    {
        $http = new HttpFactory();
        $http->fake([
            '*' => HttpFactory::response(
                ['status' => 'success', 'data' => ['user_id' => self::AUTH_USER_ID]],
                200,
            ),
        ]);

        config([
            'auth_service.user_service.base_url'    => 'http://user-service',
            'auth_service.user_service.service_key' => 'key',
        ]);

        $client = new UserServiceClient($http);
        $client->getClaimsForUser(self::AUTH_USER_ID, self::TENANT_ID);

        $http->assertSent(static function (Request $request): bool {
            $url = (string) $request->url();

            return str_contains($url, '/api/internal/v1/users/' . self::AUTH_USER_ID . '/claims')
                && str_contains($url, 'tenant_id=' . self::TENANT_ID);
        });
    }
}
