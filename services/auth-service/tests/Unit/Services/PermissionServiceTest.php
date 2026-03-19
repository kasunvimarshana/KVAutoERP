<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    private Mockery\MockInterface $roleRepository;
    private Mockery\MockInterface $permissionRepository;
    private PermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleRepository       = Mockery::mock(RoleRepositoryInterface::class);
        $this->permissionRepository = Mockery::mock(PermissionRepositoryInterface::class);

        $this->permissionService = new PermissionService(
            $this->roleRepository,
            $this->permissionRepository,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_has_permission_returns_true_for_direct_permission(): void
    {
        $userId   = 'user-123';
        $tenantId = 'tenant-456';
        $permission = 'inventory.view';

        Cache::shouldReceive('remember')->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

        $this->mockUserPermissions($userId, [$permission]);

        $result = $this->permissionService->hasPermission($userId, $permission, $tenantId);

        $this->assertTrue($result);
    }

    public function test_has_permission_returns_false_for_missing_permission(): void
    {
        $userId   = 'user-123';
        $tenantId = 'tenant-456';

        Cache::shouldReceive('remember')->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

        $this->mockUserPermissions($userId, ['inventory.view']);

        $result = $this->permissionService->hasPermission($userId, 'inventory.delete', $tenantId);

        $this->assertFalse($result);
    }

    public function test_has_permission_supports_wildcard_permissions(): void
    {
        $userId   = 'user-123';
        $tenantId = 'tenant-456';

        Cache::shouldReceive('remember')->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

        $this->mockUserPermissions($userId, ['inventory.*']); // Wildcard

        $result = $this->permissionService->hasPermission($userId, 'inventory.delete', $tenantId);

        $this->assertTrue($result);
    }

    public function test_has_role_returns_true_when_user_has_role(): void
    {
        $userId   = 'user-123';
        $tenantId = 'tenant-456';

        Cache::shouldReceive('remember')->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

        $this->roleRepository->shouldReceive('getUserRoles')
            ->with($userId)
            ->andReturn($this->makeRoleCollection(['admin', 'manager']));

        $result = $this->permissionService->hasRole($userId, 'admin', $tenantId);

        $this->assertTrue($result);
    }

    public function test_has_role_returns_false_when_user_lacks_role(): void
    {
        $userId   = 'user-123';
        $tenantId = 'tenant-456';

        Cache::shouldReceive('remember')->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

        $this->roleRepository->shouldReceive('getUserRoles')
            ->with($userId)
            ->andReturn($this->makeRoleCollection(['viewer']));

        $result = $this->permissionService->hasRole($userId, 'admin', $tenantId);

        $this->assertFalse($result);
    }

    public function test_invalidate_cache_removes_role_and_permission_cache_keys(): void
    {
        $userId   = 'user-123';
        $tenantId = 'tenant-456';

        Cache::shouldReceive('forget')
            ->with("tenant:{$tenantId}:user:{$userId}:permissions")
            ->once();
        Cache::shouldReceive('forget')
            ->with("tenant:{$tenantId}:user:{$userId}:roles")
            ->once();

        $this->permissionService->invalidateCache($userId, $tenantId);
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function mockUserPermissions(string $userId, array $permissionNames): void
    {
        $permissions = new Collection(
            array_map(function (string $name) {
                $p = Mockery::mock(Permission::class)->makePartial();
                $p->name = $name;
                return $p;
            }, $permissionNames),
        );

        $emptyRoles = new Collection();
        $emptyRoles->each(function () {});

        $this->roleRepository->shouldReceive('getUserRoles')
            ->with($userId)
            ->andReturn(new Collection());

        $this->permissionRepository->shouldReceive('getUserPermissions')
            ->with($userId)
            ->andReturn($permissions);
    }

    private function makeRoleCollection(array $roleNames): Collection
    {
        return new Collection(
            array_map(function (string $name) {
                $r = Mockery::mock(Role::class)->makePartial();
                $r->name = $name;
                return $r;
            }, $roleNames),
        );
    }
}
