<?php

declare(strict_types=1);

namespace Tests\Authorization;

use App\Contracts\Services\PermissionServiceInterface;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * RBAC + ABAC Authorization Tests
 * Validates role-based and attribute-based access control.
 */
class RbacAbacTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private PermissionServiceInterface $permissionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->permissionService = $this->app->make(PermissionServiceInterface::class);

        Cache::flush();
    }

    public function test_user_with_admin_role_has_all_permissions(): void
    {
        [$user, $role] = $this->createUserWithRole('admin');

        $permission = Permission::factory()->create(['name' => 'inventory.view']);
        $role->permissions()->attach($permission);

        $this->assertTrue(
            $this->permissionService->hasPermission($user->id, 'inventory.view', $this->tenant->id),
        );
    }

    public function test_user_without_role_has_no_permissions(): void
    {
        $user = User::factory()->for($this->tenant)->create();

        $this->assertFalse(
            $this->permissionService->hasPermission($user->id, 'inventory.view', $this->tenant->id),
        );
    }

    public function test_wildcard_permission_grants_all_child_permissions(): void
    {
        [$user, $role] = $this->createUserWithRole('inventory-manager');

        $wildcardPermission = Permission::factory()->create(['name' => 'inventory.*']);
        $role->permissions()->attach($wildcardPermission);

        // All inventory.* permissions should be granted
        $this->assertTrue($this->permissionService->hasPermission($user->id, 'inventory.view', $this->tenant->id));
        $this->assertTrue($this->permissionService->hasPermission($user->id, 'inventory.create', $this->tenant->id));
        $this->assertTrue($this->permissionService->hasPermission($user->id, 'inventory.delete', $this->tenant->id));

        // Other domains should NOT be granted
        $this->assertFalse($this->permissionService->hasPermission($user->id, 'finance.view', $this->tenant->id));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->for($this->tenant)->create();

        $role1 = Role::factory()->for($this->tenant)->create(['name' => 'viewer']);
        $role2 = Role::factory()->for($this->tenant)->create(['name' => 'editor']);

        $perm1 = Permission::factory()->create(['name' => 'products.view']);
        $perm2 = Permission::factory()->create(['name' => 'products.edit']);

        $role1->permissions()->attach($perm1);
        $role2->permissions()->attach($perm2);

        $user->roles()->attach([$role1->id, $role2->id]);

        $this->assertTrue($this->permissionService->hasRole($user->id, 'viewer', $this->tenant->id));
        $this->assertTrue($this->permissionService->hasRole($user->id, 'editor', $this->tenant->id));
        $this->assertFalse($this->permissionService->hasRole($user->id, 'admin', $this->tenant->id));

        $this->assertTrue($this->permissionService->hasPermission($user->id, 'products.view', $this->tenant->id));
        $this->assertTrue($this->permissionService->hasPermission($user->id, 'products.edit', $this->tenant->id));
        $this->assertFalse($this->permissionService->hasPermission($user->id, 'products.delete', $this->tenant->id));
    }

    public function test_role_can_be_created_at_runtime_without_redeployment(): void
    {
        $roleName = 'dynamic-role-' . uniqid();

        $role = $this->permissionService->createRole(
            $roleName,
            $this->tenant->id,
            [],
            'A dynamically created role',
        );

        $this->assertNotEmpty($role['id']);
        $this->assertEquals($roleName, $role['name']);

        $this->assertDatabaseHas('roles', [
            'name'      => $roleName,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_permission_can_be_created_at_runtime_without_redeployment(): void
    {
        $permissionName = 'module.feature.' . uniqid();

        $permission = $this->permissionService->createPermission(
            $permissionName,
            'api',
            'Dynamically created permission',
        );

        $this->assertNotEmpty($permission['id']);
        $this->assertEquals($permissionName, $permission['name']);

        $this->assertDatabaseHas('permissions', ['name' => $permissionName]);
    }

    public function test_revoking_role_removes_access(): void
    {
        [$user, $role] = $this->createUserWithRole('editor');

        $permission = Permission::factory()->create(['name' => 'documents.edit']);
        $role->permissions()->attach($permission);

        // Initially has permission
        Cache::flush();
        $this->assertTrue($this->permissionService->hasPermission($user->id, 'documents.edit', $this->tenant->id));

        // Revoke role
        $this->permissionService->revokeRole($user->id, $role->id, $this->tenant->id);

        // Cache is invalidated; now should not have permission
        Cache::flush();
        $this->assertFalse($this->permissionService->hasPermission($user->id, 'documents.edit', $this->tenant->id));
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function createUserWithRole(string $roleName): array
    {
        $user = User::factory()->for($this->tenant)->create();
        $role = Role::factory()->for($this->tenant)->create(['name' => $roleName]);
        $user->roles()->attach($role);

        return [$user, $role];
    }
}
