<?php

namespace Tests\Feature\Api\V1;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'is_active' => true,
            'plan' => 'basic',
        ]);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200)
            ->assertJson(['status' => 'ok', 'service' => 'auth-service']);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'tenant_slug' => 'test-tenant',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'roles'],
                'token' => ['access_token', 'token_type'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        app()->instance('current_tenant_id', $this->tenant->id);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Existing User',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'roles' => ['staff'],
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'tenant_slug' => 'test-tenant',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        app()->instance('current_tenant_id', $this->tenant->id);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'roles' => ['staff'],
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
            'tenant_id' => $this->tenant->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'email'],
                'token' => ['access_token', 'token_type', 'tenant_id'],
            ]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'notexist@example.com',
            'password' => 'wrongpass',
            'tenant_id' => $this->tenant->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }
}
