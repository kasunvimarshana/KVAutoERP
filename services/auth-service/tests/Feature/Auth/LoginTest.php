<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create([
            'is_active' => true,
        ]);
    }

    public function test_login_returns_401_with_invalid_credentials(): void
    {
        $user = User::factory()->for($this->tenant)->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => $user->email,
            'password'  => 'wrong-password',
            'tenant_id' => $this->tenant->id,
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_login_returns_422_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password', 'tenant_id', 'device_id']);
    }

    public function test_login_returns_422_with_invalid_tenant_uuid(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'test@example.com',
            'password'  => 'password123',
            'tenant_id' => 'not-a-uuid',
            'device_id' => 'device-001',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tenant_id']);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('service', 'auth-service');
    }

    public function test_login_returns_429_when_rate_limit_exceeded(): void
    {
        // This test is conceptual — rate limiting relies on Redis in production
        // In the test environment, the rate limiter uses the array driver
        $user = User::factory()->for($this->tenant)->create([
            'password' => Hash::make('correct-password'),
        ]);

        $maxAttempts = config('rate_limit.login.max_attempts', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email'     => $user->email,
                'password'  => 'wrong-password',
                'tenant_id' => $this->tenant->id,
                'device_id' => 'test-device-001',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => $user->email,
            'password'  => 'wrong-password',
            'tenant_id' => $this->tenant->id,
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(429);
    }
}
