<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for PermissionController.
 *
 * Covers listing, creating, updating, and deleting permissions.
 * JWT auth is bypassed via middleware mock.
 */
class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function withJwtHeaders(string $userId = 'user-1', string $tenantId = 'tenant-1'): static
    {
        $this->mock(\App\Http\Middleware\VerifyJwtToken::class, function ($mock) use ($userId, $tenantId): void {
            $mock->shouldReceive('handle')->andReturnUsing(function ($request, $next) use ($userId, $tenantId) {
                $request->attributes->set('jwt_claims', [
                    'sub'         => $userId,
                    'tenant_id'   => $tenantId,
                    'roles'       => ['admin'],
                    'permissions' => ['permissions.manage'],
                ]);
                $request->attributes->set('user_id', $userId);
                $request->attributes->set('tenant_id', $tenantId);
                $request->attributes->set('roles', ['admin']);
                $request->attributes->set('permissions', ['permissions.manage']);

                return $next($request);
            });
        });

        return $this;
    }

    private function makePermission(array $overrides = []): Permission
    {
        $name = $overrides['name'] ?? 'permission.' . Str::random(6);

        return Permission::create(array_merge([
            'id'          => (string) Str::uuid(),
            'name'        => $name,
            'slug'        => $overrides['slug'] ?? Str::slug($name),
            'description' => 'Test permission',
            'group'       => $overrides['group'] ?? null,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/permissions
    // ──────────────────────────────────────────────────────────

    public function test_index_returns_all_permissions(): void
    {
        $this->makePermission(['name' => 'users:read',    'slug' => 'users-read']);
        $this->makePermission(['name' => 'products:read', 'slug' => 'products-read']);

        $this->withJwtHeaders()
            ->getJson('/api/v1/permissions')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/permissions')
            ->assertStatus(401);
    }

    public function test_index_returns_empty_array_when_no_permissions(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/permissions')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/permissions/{id}
    // ──────────────────────────────────────────────────────────

    public function test_show_returns_permission_by_id(): void
    {
        $permission = $this->makePermission(['name' => 'orders:create', 'slug' => 'orders-create']);

        $this->withJwtHeaders()
            ->getJson("/api/v1/permissions/{$permission->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $permission->id)
            ->assertJsonPath('data.name', 'orders:create');
    }

    public function test_show_returns_404_for_nonexistent_permission(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/permissions/' . Str::uuid())
            ->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/permissions
    // ──────────────────────────────────────────────────────────

    public function test_store_creates_permission(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/permissions', [
                'name'        => 'inventory:view',
                'slug'        => 'inventory-view',
                'description' => 'View inventory items',
                'group'       => 'inventory',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'inventory:view')
            ->assertJsonPath('data.slug', 'inventory-view');

        $this->assertDatabaseHas('permissions', ['slug' => 'inventory-view']);
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/permissions', ['slug' => 'no-name'])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_store_returns_422_when_slug_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/permissions', ['name' => 'no-slug'])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_store_returns_422_for_duplicate_name(): void
    {
        $this->makePermission(['name' => 'finance:read', 'slug' => 'finance-read']);

        $this->withJwtHeaders()
            ->postJson('/api/v1/permissions', [
                'name' => 'finance:read',
                'slug' => 'finance-read-2',
            ])
            ->assertUnprocessable();
    }

    public function test_store_returns_422_for_duplicate_slug(): void
    {
        $this->makePermission(['name' => 'crm:view', 'slug' => 'crm-view']);

        $this->withJwtHeaders()
            ->postJson('/api/v1/permissions', [
                'name' => 'crm:view:new',
                'slug' => 'crm-view',
            ])
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/permissions/{id}
    // ──────────────────────────────────────────────────────────

    public function test_update_modifies_permission(): void
    {
        $permission = $this->makePermission(['name' => 'reports:view', 'slug' => 'reports-view']);

        $this->withJwtHeaders()
            ->putJson("/api/v1/permissions/{$permission->id}", [
                'description' => 'Updated description',
                'group'       => 'reporting',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.description', 'Updated description')
            ->assertJsonPath('data.group', 'reporting');

        $this->assertDatabaseHas('permissions', [
            'id'    => $permission->id,
            'group' => 'reporting',
        ]);
    }

    public function test_update_returns_404_for_nonexistent_permission(): void
    {
        $this->withJwtHeaders()
            ->putJson('/api/v1/permissions/' . Str::uuid(), ['description' => 'x'])
            ->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/permissions/{id}
    // ──────────────────────────────────────────────────────────

    public function test_destroy_deletes_permission(): void
    {
        $permission = $this->makePermission(['name' => 'warehouse:manage', 'slug' => 'warehouse-manage']);

        $this->withJwtHeaders()
            ->deleteJson("/api/v1/permissions/{$permission->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id, 'deleted_at' => null]);
    }

    public function test_destroy_returns_404_for_nonexistent_permission(): void
    {
        $this->withJwtHeaders()
            ->deleteJson('/api/v1/permissions/' . Str::uuid())
            ->assertNotFound();
    }
}
