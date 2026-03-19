<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for RoleController.
 *
 * Covers CRUD operations, role assignment/revocation, and permission
 * synchronisation for tenant-scoped roles.
 */
class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /** Bypass JWT verification and inject tenant/user context. */
    private function withJwtHeaders(string $userId = 'user-1', string $tenantId = 'tenant-1'): static
    {
        $this->mock(\App\Http\Middleware\VerifyJwtToken::class, function ($mock) use ($userId, $tenantId): void {
            $mock->shouldReceive('handle')->andReturnUsing(function ($request, $next) use ($userId, $tenantId) {
                $request->attributes->set('jwt_claims', [
                    'sub'         => $userId,
                    'tenant_id'   => $tenantId,
                    'roles'       => ['admin'],
                    'permissions' => ['roles.manage'],
                ]);
                $request->attributes->set('user_id', $userId);
                $request->attributes->set('tenant_id', $tenantId);
                $request->attributes->set('roles', ['admin']);
                $request->attributes->set('permissions', ['roles.manage']);
                return $next($request);
            });
        });

        return $this;
    }

    private function makeRole(array $overrides = []): Role
    {
        return Role::create(array_merge([
            'id'          => (string) Str::uuid(),
            'name'        => 'Role ' . Str::random(4),
            'slug'        => 'role-' . Str::random(4),
            'tenant_id'   => null,
            'description' => 'Test role',
        ], $overrides));
    }

    private function makePermission(string $name = null): Permission
    {
        $name = $name ?? 'permission.' . Str::random(6);

        return Permission::create([
            'id'          => (string) Str::uuid(),
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => 'Test permission',
        ]);
    }

    private function makeUser(string $tenantId = null): User
    {
        return User::create([
            'id'        => (string) Str::uuid(),
            'name'      => 'Test User',
            'email'     => 'user_' . Str::random(6) . '@example.com',
            'password'  => Hash::make('password'),
            'status'    => 'active',
            'tenant_id' => $tenantId,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/roles
    // ──────────────────────────────────────────────────────────

    public function test_index_returns_roles_for_tenant(): void
    {
        $tenantId = (string) Str::uuid();
        $this->makeRole(['tenant_id' => $tenantId]);
        $this->makeRole(['tenant_id' => $tenantId]);

        $this->withJwtHeaders(tenantId: $tenantId)
            ->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'success', 'message']);
    }

    public function test_index_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/roles')
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/roles/{id}
    // ──────────────────────────────────────────────────────────

    public function test_show_returns_role_by_id(): void
    {
        $role = $this->makeRole();

        $this->withJwtHeaders()
            ->getJson("/api/v1/roles/{$role->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $role->id)
            ->assertJsonPath('data.name', $role->name);
    }

    public function test_show_returns_404_for_nonexistent_role(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/roles/' . Str::uuid())
            ->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/roles
    // ──────────────────────────────────────────────────────────

    public function test_store_creates_role(): void
    {
        $tenantId = (string) Str::uuid();

        $this->withJwtHeaders(tenantId: $tenantId)
            ->postJson('/api/v1/roles', [
                'name'        => 'Manager',
                'slug'        => 'manager',
                'description' => 'Manages staff',
                'tenant_id'   => $tenantId,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Manager');

        $this->assertDatabaseHas('roles', ['slug' => 'manager']);
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/roles', ['slug' => 'no-name'])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/roles/{id}
    // ──────────────────────────────────────────────────────────

    public function test_update_modifies_role(): void
    {
        $role = $this->makeRole(['name' => 'Old Name']);

        $this->withJwtHeaders()
            ->putJson("/api/v1/roles/{$role->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'New Name']);
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/roles/{id}
    // ──────────────────────────────────────────────────────────

    public function test_destroy_soft_deletes_role(): void
    {
        $role = $this->makeRole();

        $this->withJwtHeaders()
            ->deleteJson("/api/v1/roles/{$role->id}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/roles/assign
    // ──────────────────────────────────────────────────────────

    public function test_assign_role_to_user(): void
    {
        $tenantId = (string) Str::uuid();

        // Create a tenant so foreign-key constraints are satisfied
        Tenant::create([
            'id'     => $tenantId,
            'name'   => 'Test Tenant',
            'slug'   => 'test-tenant-' . Str::random(4),
            'status' => 'active',
        ]);

        $user = $this->makeUser($tenantId);
        $role = $this->makeRole(['tenant_id' => $tenantId]);

        $this->withJwtHeaders(tenantId: $tenantId)
            ->postJson('/api/v1/roles/assign', [
                'user_id'   => $user->id,
                'role_id'   => $role->id,
                'tenant_id' => $tenantId,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Role assigned successfully');

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_assign_role_returns_422_for_missing_fields(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/roles/assign', [])
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/roles/revoke
    // ──────────────────────────────────────────────────────────

    public function test_revoke_role_from_user(): void
    {
        $tenantId = (string) Str::uuid();

        Tenant::create([
            'id'     => $tenantId,
            'name'   => 'Test Tenant',
            'slug'   => 'test-tenant-' . Str::random(4),
            'status' => 'active',
        ]);

        $user = $this->makeUser($tenantId);
        $role = $this->makeRole(['tenant_id' => $tenantId]);

        // Assign first, then revoke
        $user->roles()->attach($role->id, ['tenant_id' => $tenantId]);

        $this->withJwtHeaders(tenantId: $tenantId)
            ->postJson('/api/v1/roles/revoke', [
                'user_id'   => $user->id,
                'role_id'   => $role->id,
                'tenant_id' => $tenantId,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Role revoked successfully');
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/roles/{id}/permissions
    // ──────────────────────────────────────────────────────────

    public function test_permissions_returns_role_permission_list(): void
    {
        $role       = $this->makeRole();
        $permission = $this->makePermission('users:read');

        $role->permissions()->attach($permission->id);

        $this->withJwtHeaders()
            ->getJson("/api/v1/roles/{$role->id}/permissions")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/roles/{id}/permissions
    // ──────────────────────────────────────────────────────────

    public function test_sync_permissions_updates_role_permissions(): void
    {
        $role       = $this->makeRole();
        $permission = $this->makePermission('products:read');

        $this->withJwtHeaders()
            ->putJson("/api/v1/roles/{$role->id}/permissions", [
                'permission_ids' => [$permission->id],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Permissions synced successfully');

        $this->assertDatabaseHas('permission_role', [
            'role_id'       => $role->id,
            'permission_id' => $permission->id,
        ]);
    }
}
