<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Unit tests for RoleService.
 *
 * Tests role CRUD, assignment/revocation, and permission synchronization.
 */
class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleService();
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function makeRole(array $overrides = []): Role
    {
        return Role::create(array_merge([
            'id'   => (string) Str::uuid(),
            'name' => 'Role ' . Str::random(4),
            'slug' => 'role-' . Str::random(4),
        ], $overrides));
    }

    private function makePermission(string $slug = null): Permission
    {
        $slug = $slug ?? 'perm-' . Str::random(6);

        return Permission::create([
            'id'   => (string) Str::uuid(),
            'name' => $slug,
            'slug' => $slug,
        ]);
    }

    private function makeUser(?string $tenantId = null): User
    {
        return User::create([
            'id'        => (string) Str::uuid(),
            'name'      => 'User ' . Str::random(4),
            'email'     => 'u_' . Str::random(6) . '@example.com',
            'password'  => Hash::make('password'),
            'tenant_id' => $tenantId,
        ]);
    }

    private function makeTenant(): Tenant
    {
        return Tenant::create([
            'id'     => (string) Str::uuid(),
            'name'   => 'Tenant ' . Str::random(4),
            'slug'   => 'tenant-' . Str::random(6),
            'status' => 'active',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // findById
    // ──────────────────────────────────────────────────────────

    public function test_find_by_id_returns_role(): void
    {
        $role   = $this->makeRole(['name' => 'Admin', 'slug' => 'admin']);
        $result = $this->service->findById($role->id);

        $this->assertIsArray($result);
        $this->assertEquals($role->id, $result['id']);
        $this->assertEquals('Admin', $result['name']);
    }

    public function test_find_by_id_returns_null_for_missing_role(): void
    {
        $result = $this->service->findById((string) Str::uuid());

        $this->assertNull($result);
    }

    // ──────────────────────────────────────────────────────────
    // create
    // ──────────────────────────────────────────────────────────

    public function test_create_stores_role_in_database(): void
    {
        $tenantId = (string) Str::uuid();
        $result   = $this->service->create([
            'name'        => 'Manager',
            'slug'        => 'manager',
            'tenant_id'   => $tenantId,
            'description' => 'Manages staff',
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('Manager', $result['name']);
        $this->assertEquals($tenantId, $result['tenant_id']);
        $this->assertDatabaseHas('roles', ['slug' => 'manager']);
    }

    // ──────────────────────────────────────────────────────────
    // update
    // ──────────────────────────────────────────────────────────

    public function test_update_modifies_role(): void
    {
        $role   = $this->makeRole(['name' => 'Old Name', 'slug' => 'old-name']);
        $result = $this->service->update($role->id, ['name' => 'New Name']);

        $this->assertEquals('New Name', $result['name']);
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'New Name']);
    }

    // ──────────────────────────────────────────────────────────
    // delete
    // ──────────────────────────────────────────────────────────

    public function test_delete_removes_role(): void
    {
        $role = $this->makeRole();
        $this->service->delete($role->id);

        $this->assertDatabaseMissing('roles', ['id' => $role->id, 'deleted_at' => null]);
    }

    // ──────────────────────────────────────────────────────────
    // assignRole / revokeRole
    // ──────────────────────────────────────────────────────────

    public function test_assign_role_creates_role_user_record(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);
        $role   = $this->makeRole(['tenant_id' => $tenant->id]);

        $this->service->assignRole($user->id, $role->id, $tenant->id);

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_assign_role_is_idempotent(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);
        $role   = $this->makeRole(['tenant_id' => $tenant->id]);

        $this->service->assignRole($user->id, $role->id, $tenant->id);
        $this->service->assignRole($user->id, $role->id, $tenant->id); // second call should not throw

        $this->assertDatabaseCount('role_user', 1);
    }

    public function test_revoke_role_removes_role_user_record(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);
        $role   = $this->makeRole(['tenant_id' => $tenant->id]);

        $this->service->assignRole($user->id, $role->id, $tenant->id);
        $this->service->revokeRole($user->id, $role->id, $tenant->id);

        $this->assertDatabaseMissing('role_user', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // getUserRoles
    // ──────────────────────────────────────────────────────────

    public function test_get_user_roles_returns_assigned_roles(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);
        $role   = $this->makeRole(['slug' => 'viewer', 'tenant_id' => $tenant->id]);

        $this->service->assignRole($user->id, $role->id, $tenant->id);

        $roles = $this->service->getUserRoles($user->id, $tenant->id);

        $this->assertCount(1, $roles);
        $this->assertEquals('viewer', $roles[0]['slug']);
    }

    public function test_get_user_roles_returns_empty_for_no_assignment(): void
    {
        $user  = $this->makeUser();
        $roles = $this->service->getUserRoles($user->id);

        $this->assertIsArray($roles);
        $this->assertEmpty($roles);
    }

    // ──────────────────────────────────────────────────────────
    // listForTenant
    // ──────────────────────────────────────────────────────────

    public function test_list_for_tenant_returns_tenant_and_global_roles(): void
    {
        $tenantId = (string) Str::uuid();
        $this->makeRole(['name' => 'Tenant Role',  'slug' => 'tenant-role',  'tenant_id' => $tenantId]);
        $this->makeRole(['name' => 'Global Role',  'slug' => 'global-role',  'tenant_id' => null]);

        $roles = $this->service->listForTenant($tenantId);

        $slugs = array_column($roles, 'slug');
        $this->assertContains('tenant-role', $slugs);
        $this->assertContains('global-role', $slugs);
    }

    // ──────────────────────────────────────────────────────────
    // syncPermissions
    // ──────────────────────────────────────────────────────────

    public function test_sync_permissions_associates_permissions_with_role(): void
    {
        $role  = $this->makeRole();
        $perm1 = $this->makePermission('users:read');
        $perm2 = $this->makePermission('users:write');

        $this->service->syncPermissions($role->id, [$perm1->id, $perm2->id]);

        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $perm1->id]);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $perm2->id]);
    }

    public function test_sync_permissions_replaces_existing_permissions(): void
    {
        $role  = $this->makeRole();
        $perm1 = $this->makePermission('old:perm');
        $perm2 = $this->makePermission('new:perm');

        $this->service->syncPermissions($role->id, [$perm1->id]);
        $this->service->syncPermissions($role->id, [$perm2->id]);

        $this->assertDatabaseMissing('permission_role', ['role_id' => $role->id, 'permission_id' => $perm1->id]);
        $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $perm2->id]);
    }

    public function test_sync_permissions_clears_all_when_empty_array_given(): void
    {
        $role = $this->makeRole();
        $perm = $this->makePermission('to-be-cleared');

        $this->service->syncPermissions($role->id, [$perm->id]);
        $this->service->syncPermissions($role->id, []);

        $this->assertDatabaseMissing('permission_role', ['role_id' => $role->id]);
    }
}
