<?php

declare(strict_types=1);

namespace Tests\Tenant;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tenant Isolation Tests
 * Ensures that data and operations are strictly isolated per tenant.
 * Cross-tenant data leakage must NEVER occur.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;
    private Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::factory()->create(['slug' => 'tenant-a']);
        $this->tenantB = Tenant::factory()->create(['slug' => 'tenant-b']);
    }

    public function test_user_in_tenant_a_cannot_be_found_in_tenant_b_scope(): void
    {
        $userA = User::factory()->for($this->tenantA)->create([
            'email' => 'alice@tenant-a.com',
        ]);

        // Query scoped to tenant B should NOT return tenant A's user
        $found = User::forTenant($this->tenantB->id)
            ->where('email', $userA->email)
            ->first();

        $this->assertNull($found);
    }

    public function test_same_email_can_exist_in_different_tenants(): void
    {
        $email = 'user@example.com';

        $userA = User::factory()->for($this->tenantA)->create(['email' => $email]);
        $userB = User::factory()->for($this->tenantB)->create(['email' => $email]);

        $this->assertNotEquals($userA->id, $userB->id);

        $foundA = User::forTenant($this->tenantA->id)->where('email', $email)->first();
        $foundB = User::forTenant($this->tenantB->id)->where('email', $email)->first();

        $this->assertEquals($userA->id, $foundA->id);
        $this->assertEquals($userB->id, $foundB->id);
    }

    public function test_user_count_is_isolated_per_tenant(): void
    {
        User::factory()->for($this->tenantA)->count(3)->create();
        User::factory()->for($this->tenantB)->count(5)->create();

        $countA = User::forTenant($this->tenantA->id)->count();
        $countB = User::forTenant($this->tenantB->id)->count();

        $this->assertEquals(3, $countA);
        $this->assertEquals(5, $countB);
    }

    public function test_login_with_tenant_b_credentials_fails_for_tenant_a(): void
    {
        User::factory()->for($this->tenantB)->create([
            'email'    => 'bob@tenant-b.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt login for tenant B user but supplying tenant A's ID
        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'bob@tenant-b.com',
            'password'  => 'password123',
            'tenant_id' => $this->tenantA->id, // Wrong tenant!
            'device_id' => 'device-001',
        ]);

        $response->assertStatus(401);
    }

    public function test_tenant_feature_flags_are_isolated(): void
    {
        $this->tenantA->update(['feature_flags' => ['sso_enabled' => true]]);
        $this->tenantB->update(['feature_flags' => ['sso_enabled' => false]]);

        $this->tenantA->refresh();
        $this->tenantB->refresh();

        $this->assertTrue($this->tenantA->isFeatureEnabled('sso_enabled'));
        $this->assertFalse($this->tenantB->isFeatureEnabled('sso_enabled'));
    }

    public function test_tenant_token_lifetimes_are_independent(): void
    {
        $this->tenantA->update(['token_lifetimes' => ['access' => 30, 'refresh' => 86400]]);
        $this->tenantB->update(['token_lifetimes' => ['access' => 5, 'refresh' => 1440]]);

        $this->tenantA->refresh();
        $this->tenantB->refresh();

        $this->assertEquals(30, $this->tenantA->getAccessTokenTtl());
        $this->assertEquals(5, $this->tenantB->getAccessTokenTtl());
    }
}
