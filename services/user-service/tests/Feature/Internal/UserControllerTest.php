<?php

declare(strict_types=1);

namespace Tests\Feature\Internal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $serviceToken = 'test-service-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.auth_service_token' => $this->serviceToken]);
    }

    private function serviceHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->serviceToken}"];
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'id'       => (string) Str::uuid(),
            'name'     => 'Test User',
            'email'    => 'test_'.Str::random(6).'@example.com',
            'password' => Hash::make('secret-password'),
            'status'   => 'active',
        ], $overrides));
    }

    public function test_find_by_id_with_valid_service_token(): void
    {
        $user = $this->makeUser();

        $response = $this->getJson(
            "/api/v1/internal/users/{$user->id}",
            $this->serviceHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_find_by_email_with_valid_service_token(): void
    {
        $user = $this->makeUser(['email' => 'findme@example.com']);

        $response = $this->getJson(
            '/api/v1/internal/users/by-email?email=findme@example.com',
            $this->serviceHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'findme@example.com');
    }

    public function test_validate_credentials_returns_valid_true(): void
    {
        $user = $this->makeUser(['password' => Hash::make('my-secret')]);

        $response = $this->postJson(
            '/api/v1/internal/users/validate-credentials',
            ['user_id' => $user->id, 'password' => 'my-secret'],
            $this->serviceHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.valid', true);
    }

    public function test_unauthorized_without_service_token(): void
    {
        $user = $this->makeUser();

        $this->getJson("/api/v1/internal/users/{$user->id}")
            ->assertStatus(403);
    }
}
