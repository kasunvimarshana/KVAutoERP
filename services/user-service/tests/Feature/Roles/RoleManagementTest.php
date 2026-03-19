<?php

declare(strict_types=1);

namespace Tests\Feature\Roles;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Middleware\VerifyJwtMiddleware;
use App\Models\Role;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for Role management endpoints.
 *
 * JWT middleware is bypassed; service calls are mocked.
 * No database interaction — all data access is via mocked services.
 */
final class RoleManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setTenantContext();
        $this->withoutMiddleware([VerifyJwtMiddleware::class]);
    }

    /**
     * Build a Role model instance for test assertions.
     *
     * @param  array<string, mixed>  $attributes
     * @return Role
     */
    private function makeRoleMock(array $attributes = []): Role
    {
        $role = new Role(array_merge([
            'id'              => 'aaaabbbb-cccc-dddd-eeee-ffffaaaabbbb',
            'tenant_id'       => self::TEST_TENANT_ID,
            'name'            => 'Administrator',
            'slug'            => 'admin',
            'description'     => 'Full administrator access.',
            'hierarchy_level' => 100,
            'is_system'       => true,
        ], $attributes));

        $role->setRelation('permissions', collect());

        return $role;
    }

    #[Test]
    public function it_creates_a_role_successfully(): void
    {
        $role = $this->makeRoleMock();

        $service = Mockery::mock(RoleServiceInterface::class);
        $service->shouldReceive('createRole')
            ->once()
            ->andReturn($role);

        $this->app->instance(RoleServiceInterface::class, $service);

        $response = $this->postJson('/api/v1/roles', [
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.slug', 'admin');
    }

    #[Test]
    public function it_assigns_permission_to_role(): void
    {
        $service = Mockery::mock(RoleServiceInterface::class);
        $service->shouldReceive('assignPermission')
            ->once()
            ->with('aaaabbbb-cccc-dddd-eeee-ffffaaaabbbb', '11112222-3333-4444-5555-666677778888');

        $this->app->instance(RoleServiceInterface::class, $service);

        $response = $this->postJson(
            '/api/v1/roles/aaaabbbb-cccc-dddd-eeee-ffffaaaabbbb/permissions',
            ['permission_id' => '11112222-3333-4444-5555-666677778888'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    #[Test]
    public function it_revokes_permission_from_role(): void
    {
        $service = Mockery::mock(RoleServiceInterface::class);
        $service->shouldReceive('revokePermission')
            ->once()
            ->with('aaaabbbb-cccc-dddd-eeee-ffffaaaabbbb', '11112222-3333-4444-5555-666677778888');

        $this->app->instance(RoleServiceInterface::class, $service);

        $response = $this->deleteJson(
            '/api/v1/roles/aaaabbbb-cccc-dddd-eeee-ffffaaaabbbb/permissions/11112222-3333-4444-5555-666677778888',
        );

        $response->assertStatus(204);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
