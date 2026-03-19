<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Policy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for PolicyController (ABAC policy CRUD and evaluation).
 */
class PolicyControllerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function withJwtHeaders(
        string $userId   = 'user-1',
        string $tenantId = 'tenant-1',
        array  $roles    = ['admin'],
    ): static {
        $this->mock(\App\Http\Middleware\VerifyJwtToken::class, function ($mock) use ($userId, $tenantId, $roles): void {
            $mock->shouldReceive('handle')->andReturnUsing(function ($request, $next) use ($userId, $tenantId, $roles) {
                $request->attributes->set('jwt_claims', [
                    'sub'         => $userId,
                    'tenant_id'   => $tenantId,
                    'roles'       => $roles,
                    'permissions' => [],
                ]);
                $request->attributes->set('user_id', $userId);
                $request->attributes->set('tenant_id', $tenantId);
                $request->attributes->set('roles', $roles);
                $request->attributes->set('permissions', []);
                return $next($request);
            });
        });

        return $this;
    }

    private function makePolicy(array $overrides = []): Policy
    {
        return Policy::create(array_merge([
            'id'        => (string) Str::uuid(),
            'name'      => 'Policy ' . Str::random(4),
            'slug'      => 'policy-' . Str::random(4),
            'effect'    => 'allow',
            'action'    => 'users:read',
            'is_active' => true,
            'priority'  => 10,
            'tenant_id' => null,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/policies
    // ──────────────────────────────────────────────────────────

    public function test_index_returns_paginated_policy_list(): void
    {
        $tenantId = (string) Str::uuid();
        $this->makePolicy(['tenant_id' => $tenantId]);
        $this->makePolicy(['tenant_id' => $tenantId]);

        $this->withJwtHeaders(tenantId: $tenantId)
            ->getJson('/api/v1/policies')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['pagination']]);
    }

    public function test_index_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/policies')
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/policies/{id}
    // ──────────────────────────────────────────────────────────

    public function test_show_returns_policy_by_id(): void
    {
        $policy = $this->makePolicy();

        $this->withJwtHeaders()
            ->getJson("/api/v1/policies/{$policy->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $policy->id)
            ->assertJsonPath('data.action', 'users:read');
    }

    public function test_show_returns_404_for_nonexistent_policy(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/policies/' . Str::uuid())
            ->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/policies
    // ──────────────────────────────────────────────────────────

    public function test_store_creates_allow_policy(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/policies', [
                'name'                => 'Allow users read',
                'effect'              => 'allow',
                'action'              => 'users:read',
                'subject_conditions'  => ['roles' => ['admin', 'manager']],
                'is_active'           => true,
                'priority'            => 100,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.action', 'users:read')
            ->assertJsonPath('data.effect', 'allow');

        $this->assertDatabaseHas('policies', ['action' => 'users:read', 'effect' => 'allow']);
    }

    public function test_store_creates_deny_policy(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/policies', [
                'name'   => 'Deny guests delete',
                'effect' => 'deny',
                'action' => 'users:delete',
            ])
            ->assertCreated()
            ->assertJsonPath('data.effect', 'deny');
    }

    public function test_store_returns_422_for_invalid_effect(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/policies', [
                'name'   => 'Bad Policy',
                'effect' => 'maybe',
                'action' => 'users:read',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_store_returns_422_when_required_fields_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/policies', ['name' => 'Incomplete'])
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/policies/{id}
    // ──────────────────────────────────────────────────────────

    public function test_update_modifies_policy(): void
    {
        $policy = $this->makePolicy(['action' => 'products:read']);

        $this->withJwtHeaders()
            ->putJson("/api/v1/policies/{$policy->id}", [
                'action'    => 'products:write',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.action', 'products:write');

        $this->assertDatabaseHas('policies', ['id' => $policy->id, 'action' => 'products:write']);
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/policies/{id}
    // ──────────────────────────────────────────────────────────

    public function test_destroy_removes_policy(): void
    {
        $policy = $this->makePolicy();

        $this->withJwtHeaders()
            ->deleteJson("/api/v1/policies/{$policy->id}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/policies/evaluate
    // ──────────────────────────────────────────────────────────

    public function test_evaluate_returns_allowed_true_for_matching_allow_policy(): void
    {
        $tenantId = (string) Str::uuid();

        Policy::create([
            'id'                  => (string) Str::uuid(),
            'name'                => 'Allow admin users read',
            'slug'                => 'allow-admin-users-read-' . Str::random(4),
            'effect'              => 'allow',
            'action'              => 'users:read',
            'subject_conditions'  => ['roles' => ['admin']],
            'is_active'           => true,
            'priority'            => 10,
            'tenant_id'           => $tenantId,
        ]);

        $this->withJwtHeaders(roles: ['admin'], tenantId: $tenantId)
            ->postJson('/api/v1/policies/evaluate', [
                'action'   => 'users:read',
                'resource' => [],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['allowed']]);
    }

    public function test_evaluate_returns_denied_when_no_matching_policy(): void
    {
        $this->withJwtHeaders(roles: ['viewer'])
            ->postJson('/api/v1/policies/evaluate', [
                'action'   => 'users:delete',
                'resource' => [],
            ])
            ->assertOk()
            ->assertJsonPath('data.allowed', false);
    }

    public function test_evaluate_returns_422_when_action_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/policies/evaluate', [])
            ->assertUnprocessable();
    }
}
