<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantId;
    private string $actorId;
    private array  $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = Uuid::uuid4()->toString();
        $this->actorId  = Uuid::uuid4()->toString();
        $this->headers  = $this->serviceTokenHeaders($this->tenantId, $this->actorId);
    }

    // ─────────────────────────────────────────────────────────────────
    // Health
    // ─────────────────────────────────────────────────────────────────

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('service', 'user-service');
    }

    // ─────────────────────────────────────────────────────────────────
    // Authentication middleware
    // ─────────────────────────────────────────────────────────────────

    public function test_users_endpoint_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_users_endpoint_returns_401_with_malformed_token(): void
    {
        $response = $this->getJson('/api/v1/users', [
            'Authorization' => 'Bearer not.a.real.jwt',
            'Accept'        => 'application/json',
        ]);

        // 3-part JWT with invalid base64 payload decodes to null → 401
        $response->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────────────────
    // Index / List
    // ─────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_users_for_tenant(): void
    {
        User::factory()->count(3)->forTenant($this->tenantId)->create();
        // Different tenant – should NOT appear
        User::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/users', $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_index_filters_by_name(): void
    {
        User::factory()->forTenant($this->tenantId)->create(['name' => 'Alice Wonder']);
        User::factory()->forTenant($this->tenantId)->create(['name' => 'Bob Builder']);

        $response = $this->getJson('/api/v1/users?name=Alice', $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Alice Wonder');
    }

    public function test_index_filters_by_active_status(): void
    {
        User::factory()->forTenant($this->tenantId)->create(['is_active' => true]);
        User::factory()->forTenant($this->tenantId)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/users?is_active=1', $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.is_active', true);
    }

    // ─────────────────────────────────────────────────────────────────
    // Store / Create
    // ─────────────────────────────────────────────────────────────────

    public function test_store_creates_user_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Secret@1234',
            'password_confirmation' => 'Secret@1234',
        ], $this->headers);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonPath('data.tenant_id', $this->tenantId);

        $this->assertDatabaseHas('users', [
            'email'     => 'john@example.com',
            'tenant_id' => $this->tenantId,
        ]);
    }

    public function test_store_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/v1/users', [], $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_store_returns_422_when_email_already_exists_in_tenant(): void
    {
        User::factory()->forTenant($this->tenantId)->create(['email' => 'dupe@example.com']);

        $response = $this->postJson('/api/v1/users', [
            'name'                  => 'Duplicate',
            'email'                 => 'dupe@example.com',
            'password'              => 'Secret@1234',
            'password_confirmation' => 'Secret@1234',
        ], $this->headers);

        $response->assertStatus(422);
    }

    public function test_store_allows_same_email_in_different_tenants(): void
    {
        $otherTenantId = Uuid::uuid4()->toString();
        User::factory()->forTenant($otherTenantId)->create(['email' => 'shared@example.com']);

        $response = $this->postJson('/api/v1/users', [
            'name'                  => 'Cross Tenant User',
            'email'                 => 'shared@example.com',
            'password'              => 'Secret@1234',
            'password_confirmation' => 'Secret@1234',
        ], $this->headers);

        $response->assertStatus(201);
    }

    // ─────────────────────────────────────────────────────────────────
    // Show
    // ─────────────────────────────────────────────────────────────────

    public function test_show_returns_user_for_correct_tenant(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->create();

        $response = $this->getJson("/api/v1/users/{$user->id}", $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.tenant_id', $this->tenantId);
    }

    public function test_show_returns_422_when_user_belongs_to_different_tenant(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$otherUser->id}", $this->headers);

        // UserException::tenantMismatch returns 403, rendered as 422/403 via exception handler
        $response->assertStatus(403);
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $response = $this->getJson('/api/v1/users/' . Uuid::uuid4()->toString(), $this->headers);

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────────
    // Update
    // ─────────────────────────────────────────────────────────────────

    public function test_update_modifies_user_fields(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'New Name',
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    // ─────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────

    public function test_destroy_soft_deletes_user(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}", [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Profile
    // ─────────────────────────────────────────────────────────────────

    public function test_update_profile_creates_profile_when_none_exists(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->create();

        $response = $this->putJson("/api/v1/users/{$user->id}/profile", [
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'city'       => 'London',
            'timezone'   => 'Europe/London',
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.last_name', 'Doe')
            ->assertJsonPath('data.city', 'London');

        $this->assertDatabaseHas('user_profiles', [
            'user_id'    => $user->id,
            'first_name' => 'Jane',
        ]);
    }

    public function test_get_profile_returns_404_when_profile_not_created(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->create();

        $response = $this->getJson("/api/v1/users/{$user->id}/profile", $this->headers);

        $response->assertStatus(404);
    }

    public function test_get_profile_returns_profile_when_exists(): void
    {
        $user    = User::factory()->forTenant($this->tenantId)->create();
        $profile = UserProfile::factory()->forUser($user)->create(['first_name' => 'Alice']);

        $response = $this->getJson("/api/v1/users/{$user->id}/profile", $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Alice');
    }

    // ─────────────────────────────────────────────────────────────────
    // Status transitions
    // ─────────────────────────────────────────────────────────────────

    public function test_deactivate_sets_is_active_to_false(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->create(['is_active' => true]);

        $response = $this->postJson("/api/v1/users/{$user->id}/deactivate", [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }

    public function test_activate_sets_is_active_to_true(): void
    {
        $user = User::factory()->forTenant($this->tenantId)->inactive()->create();

        $response = $this->postJson("/api/v1/users/{$user->id}/activate", [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', true);
    }
}
