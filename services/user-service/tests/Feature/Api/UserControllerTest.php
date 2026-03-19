<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for UserController.
 *
 * Covers user CRUD, profile retrieval, and avatar upload.
 * JWT authentication is bypassed via middleware mock for speed and isolation.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /** Bypass JWT verification and inject tenant/user context into the request. */
    private function withJwtHeaders(string $userId = 'user-1', string $tenantId = 'tenant-1'): static
    {
        $this->mock(\App\Http\Middleware\VerifyJwtToken::class, function ($mock) use ($userId, $tenantId): void {
            $mock->shouldReceive('handle')->andReturnUsing(function ($request, $next) use ($userId, $tenantId) {
                $request->attributes->set('jwt_claims', [
                    'sub'         => $userId,
                    'tenant_id'   => $tenantId,
                    'roles'       => ['admin'],
                    'permissions' => ['users.manage'],
                ]);
                $request->attributes->set('user_id', $userId);
                $request->attributes->set('tenant_id', $tenantId);
                $request->attributes->set('roles', ['admin']);
                $request->attributes->set('permissions', ['users.manage']);

                return $next($request);
            });
        });

        return $this;
    }

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'id'     => (string) Str::uuid(),
            'name'   => 'Test Tenant ' . Str::random(4),
            'slug'   => 'test-' . Str::random(6),
            'status' => 'active',
        ], $overrides));
    }

    private function makeUser(string $tenantId, array $overrides = []): User
    {
        return User::create(array_merge([
            'id'        => (string) Str::uuid(),
            'name'      => 'Test User ' . Str::random(4),
            'email'     => 'user_' . Str::random(6) . '@example.com',
            'password'  => Hash::make('Password1!'),
            'status'    => 'active',
            'tenant_id' => $tenantId,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/users
    // ──────────────────────────────────────────────────────────

    public function test_index_returns_users_for_tenant(): void
    {
        $tenant = $this->makeTenant();
        $this->makeUser($tenant->id);
        $this->makeUser($tenant->id);

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'data', 'meta', 'message']);
    }

    public function test_index_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/users')
            ->assertStatus(401);
    }

    public function test_index_supports_status_filter(): void
    {
        $tenant = $this->makeTenant();
        $this->makeUser($tenant->id, ['status' => 'active']);
        $this->makeUser($tenant->id, ['status' => 'inactive']);

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->getJson('/api/v1/users?status=active')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/users/{id}
    // ──────────────────────────────────────────────────────────

    public function test_show_returns_user_by_id(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/users/' . Str::uuid())
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/users
    // ──────────────────────────────────────────────────────────

    public function test_store_creates_user(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->postJson('/api/v1/users', [
                'name'      => 'New User',
                'email'     => 'newuser@example.com',
                'password'  => 'Password1!',
                'tenant_id' => $tenant->id,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'newuser@example.com')
            ->assertJsonPath('data.name', 'New User');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_store_returns_422_when_required_fields_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/users', [
                'name' => 'Missing Email',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_store_returns_422_for_duplicate_email(): void
    {
        $tenant = $this->makeTenant();
        $this->makeUser($tenant->id, ['email' => 'duplicate@example.com']);

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->postJson('/api/v1/users', [
                'name'      => 'Duplicate',
                'email'     => 'duplicate@example.com',
                'password'  => 'Password1!',
                'tenant_id' => $tenant->id,
            ])
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/users/{id}
    // ──────────────────────────────────────────────────────────

    public function test_update_modifies_user(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id, ['name' => 'Old Name']);

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->putJson("/api/v1/users/{$user->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_update_does_not_change_password_directly(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);

        // Password key should be stripped by UpdateUserRequest
        $this->withJwtHeaders(tenantId: $tenant->id)
            ->putJson("/api/v1/users/{$user->id}", [
                'name'     => 'Updated',
                'password' => 'ShouldBeIgnored1!',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');

        // Original password must remain valid
        $fresh = User::find($user->id);
        $this->assertTrue(Hash::check('Password1!', $fresh->password));
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/users/{id}
    // ──────────────────────────────────────────────────────────

    public function test_destroy_deletes_user(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);

        $this->withJwtHeaders(tenantId: $tenant->id)
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/users/profile
    // ──────────────────────────────────────────────────────────

    public function test_profile_returns_current_user(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);

        $this->withJwtHeaders(userId: $user->id, tenantId: $tenant->id)
            ->getJson('/api/v1/users/profile')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_profile_returns_404_when_user_not_found(): void
    {
        $unknownId = (string) Str::uuid();

        $this->withJwtHeaders(userId: $unknownId)
            ->getJson('/api/v1/users/profile')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/users/{id}/avatar
    // ──────────────────────────────────────────────────────────

    public function test_upload_avatar_stores_image_and_returns_url(): void
    {
        Storage::fake('s3');

        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $this->withJwtHeaders(userId: $user->id, tenantId: $tenant->id)
            ->postJson("/api/v1/users/{$user->id}/avatar", [
                'avatar' => $file,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['avatar_url']]);
    }

    public function test_upload_avatar_returns_422_when_file_missing(): void
    {
        $tenant = $this->makeTenant();
        $user   = $this->makeUser($tenant->id);

        $this->withJwtHeaders(userId: $user->id, tenantId: $tenant->id)
            ->postJson("/api/v1/users/{$user->id}/avatar", [])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }
}
