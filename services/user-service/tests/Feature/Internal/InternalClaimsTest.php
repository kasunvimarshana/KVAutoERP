<?php

declare(strict_types=1);

namespace Tests\Feature\Internal;

use App\Contracts\Services\UserProfileServiceInterface;
use App\Http\Middleware\VerifyServiceKeyMiddleware;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the internal claims endpoint.
 *
 * Tests both the VerifyServiceKeyMiddleware authentication guard
 * and the UserProfileService::getClaimsForAuth() integration.
 * No database interaction — all service calls are mocked.
 */
final class InternalClaimsTest extends TestCase
{

    /** Service key that matches phpunit.xml ENV value. */
    private const VALID_SERVICE_KEY = 'test-service-key-12345';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setTenantContext();
    }

    #[Test]
    public function it_returns_claims_for_valid_auth_user_id(): void
    {
        $claims = [
            'user_id'         => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'branch_id'       => null,
            'location_id'     => null,
            'department_id'   => null,
            'roles'           => ['admin'],
            'permissions'     => ['users.manage', 'roles.manage'],
            'profile'         => [
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'display_name' => 'John Doe',
                'locale'       => 'en',
                'timezone'     => 'UTC',
            ],
        ];

        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('getClaimsForAuth')
            ->once()
            ->with('cccccccc-cccc-cccc-cccc-cccccccccccc', self::TEST_TENANT_ID)
            ->andReturn($claims);

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->getJson(
            '/api/internal/v1/users/cccccccc-cccc-cccc-cccc-cccccccccccc/claims?tenant_id=' . self::TEST_TENANT_ID,
            ['X-Service-Key' => self::VALID_SERVICE_KEY],
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user_id', 'cccccccc-cccc-cccc-cccc-cccccccccccc')
            ->assertJsonPath('data.tenant_id', self::TEST_TENANT_ID)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user_id',
                    'tenant_id',
                    'roles',
                    'permissions',
                    'profile',
                ],
            ]);
    }

    #[Test]
    public function it_rejects_request_without_service_key(): void
    {
        $response = $this->getJson(
            '/api/internal/v1/users/any-id/claims?tenant_id=' . self::TEST_TENANT_ID,
        );

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_rejects_request_with_invalid_service_key(): void
    {
        $response = $this->getJson(
            '/api/internal/v1/users/any-id/claims?tenant_id=' . self::TEST_TENANT_ID,
            ['X-Service-Key' => 'wrong-key'],
        );

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_returns_404_when_user_profile_not_found(): void
    {
        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('getClaimsForAuth')
            ->once()
            ->andReturn(null);

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->getJson(
            '/api/internal/v1/users/nonexistent-user-id/claims?tenant_id=' . self::TEST_TENANT_ID,
            ['X-Service-Key' => self::VALID_SERVICE_KEY],
        );

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
