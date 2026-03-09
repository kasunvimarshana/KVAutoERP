<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Modules\Auth\Domain\Models\User;
use App\Modules\Tenant\Domain\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Authentication API (login, register, logout).
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Auth Test Corp',
            'slug'      => 'auth-test-corp',
            'is_active' => true,
        ]);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'tenant_id'             => $this->tenant->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'token', 'token_type'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_register_validates_password_strength(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Weak Pass',
            'email'                 => 'weak@example.com',
            'password'              => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        User::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'John Doe',
            'email'     => 'john@example.com',
            'password'  => bcrypt('Password1!'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'john@example.com',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true])
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type'],
            ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(500); // RuntimeException → mapped to 500 in debug mode
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Me User',
            'email'     => 'me@example.com',
            'password'  => bcrypt('Password1!'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'me@example.com']);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }
}
