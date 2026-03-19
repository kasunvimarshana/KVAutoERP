<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for TenantController — IAM config, feature flags, and
 * the hierarchy endpoints.
 *
 * JWT auth is bypassed by overriding the jwt_claims request attribute
 * directly.  This keeps tests fast and decoupled from the Auth service.
 */
class TenantControllerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    /** Return headers that satisfy VerifyJwtToken middleware via mock. */
    private function authHeaders(string $tenantId = 'tenant-1'): array
    {
        // Bypass JWT verification for feature tests by setting the
        // jwt public key to an inline value that matches the mock token.
        // We use a mock on the middleware instead.
        return [];
    }

    /**
     * Create a valid JWT Bearer token mock by stubbing TokenServiceContract.
     */
    private function withJwtHeaders(string $userId = 'user-1', string $tenantId = 'tenant-1'): static
    {
        $this->mock(\App\Http\Middleware\VerifyJwtToken::class, function ($mock) use ($userId, $tenantId): void {
            $mock->shouldReceive('handle')->andReturnUsing(function ($request, $next) use ($userId, $tenantId) {
                $request->attributes->set('jwt_claims', [
                    'sub'       => $userId,
                    'tenant_id' => $tenantId,
                    'roles'     => ['admin'],
                    'permissions' => ['tenants.manage'],
                ]);
                $request->attributes->set('user_id', $userId);
                $request->attributes->set('tenant_id', $tenantId);
                $request->attributes->set('roles', ['admin']);
                $request->attributes->set('permissions', ['tenants.manage']);
                return $next($request);
            });
        });

        return $this;
    }

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'id'            => (string) Str::uuid(),
            'name'          => 'Test Tenant ' . Str::random(4),
            'slug'          => 'test-' . Str::random(4),
            'status'        => 'active',
            'iam_provider'  => 'local',
            'configuration' => [],
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/tenants/{id}/iam-config
    // ──────────────────────────────────────────────────────────

    public function test_get_iam_config_returns_tenant_provider_and_config(): void
    {
        $tenant = $this->makeTenant([
            'iam_provider'  => 'okta',
            'configuration' => ['iam' => ['domain' => 'my-okta.okta.com']],
        ]);

        $this->withJwtHeaders()
            ->getJson("/api/v1/tenants/{$tenant->id}/iam-config")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.iam_provider', 'okta')
            ->assertJsonPath('data.iam_config.domain', 'my-okta.okta.com');
    }

    public function test_get_iam_config_returns_404_for_unknown_tenant(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/tenants/' . Str::uuid() . '/iam-config')
            ->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/tenants/{id}/iam-config
    // ──────────────────────────────────────────────────────────

    public function test_update_iam_config_persists_provider_and_settings(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->putJson("/api/v1/tenants/{$tenant->id}/iam-config", [
                'iam_provider' => 'keycloak',
                'iam_config'   => [
                    'base_url'      => 'https://keycloak.example.com',
                    'realm'         => 'master',
                    'client_id'     => 'my-client',
                    'client_secret' => 'secret',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.iam_provider', 'keycloak')
            ->assertJsonPath('data.iam_config.realm', 'master');

        $this->assertDatabaseHas('tenants', [
            'id'           => $tenant->id,
            'iam_provider' => 'keycloak',
        ]);
    }

    public function test_update_iam_config_requires_valid_provider(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->putJson("/api/v1/tenants/{$tenant->id}/iam-config", [
                'iam_provider' => 'invalid-provider',
                'iam_config'   => [],
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/tenants/{id}/feature-flags
    // ──────────────────────────────────────────────────────────

    public function test_get_feature_flags_returns_correct_flags(): void
    {
        $tenant = $this->makeTenant([
            'configuration' => [
                'feature_flags' => [
                    'new_dashboard' => true,
                    'beta_api'      => false,
                ],
            ],
        ]);

        $this->withJwtHeaders()
            ->getJson("/api/v1/tenants/{$tenant->id}/feature-flags")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.feature_flags.new_dashboard', true)
            ->assertJsonPath('data.feature_flags.beta_api', false);
    }

    public function test_get_feature_flags_returns_empty_array_when_not_configured(): void
    {
        $tenant = $this->makeTenant(['configuration' => []]);

        $this->withJwtHeaders()
            ->getJson("/api/v1/tenants/{$tenant->id}/feature-flags")
            ->assertOk()
            ->assertJsonPath('data.feature_flags', []);
    }

    // ──────────────────────────────────────────────────────────
    // PUT /api/v1/tenants/{id}/feature-flags
    // ──────────────────────────────────────────────────────────

    public function test_update_feature_flags_persists_changes(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->putJson("/api/v1/tenants/{$tenant->id}/feature-flags", [
                'feature_flags' => [
                    'reporting_v2' => true,
                    'beta_pos'     => false,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.feature_flags.reporting_v2', true)
            ->assertJsonPath('data.feature_flags.beta_pos', false);
    }

    public function test_update_feature_flags_requires_array(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->putJson("/api/v1/tenants/{$tenant->id}/feature-flags", [
                'feature_flags' => 'not-an-array',
            ])
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/tenants/{id}/hierarchy
    // ──────────────────────────────────────────────────────────

    public function test_hierarchy_returns_tenant_tree(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->getJson("/api/v1/tenants/{$tenant->id}/hierarchy")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $tenant->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'organizations']]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/tenants (CRUD)
    // ──────────────────────────────────────────────────────────

    public function test_index_returns_paginated_list(): void
    {
        $this->makeTenant();
        $this->makeTenant();

        $this->withJwtHeaders()
            ->getJson('/api/v1/tenants')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['pagination']]);
    }

    public function test_show_returns_tenant_by_id(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->getJson("/api/v1/tenants/{$tenant->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $tenant->id)
            ->assertJsonPath('data.name', $tenant->name);
    }

    public function test_store_creates_new_tenant(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/tenants', [
                'name'   => 'New Corp',
                'slug'   => 'new-corp',
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Corp');

        $this->assertDatabaseHas('tenants', ['name' => 'New Corp']);
    }

    public function test_destroy_soft_deletes_tenant(): void
    {
        $tenant = $this->makeTenant();

        $this->withJwtHeaders()
            ->deleteJson("/api/v1/tenants/{$tenant->id}")
            ->assertOk();

        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    }
}
