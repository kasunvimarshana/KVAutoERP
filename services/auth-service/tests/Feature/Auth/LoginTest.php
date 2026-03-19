<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\AuthenticationException;
use App\Http\Resources\AuthTokenResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for POST /api/v1/auth/login.
 */
final class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_token_pair_on_valid_credentials(): void
    {
        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('login')
            ->once()
            ->andReturn([
                'access_token'  => 'eyJ.fake.token',
                'refresh_token' => 'refresh-token-value',
                'expires_in'    => 900,
                'token_type'    => 'Bearer',
            ]);

        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'admin@example.com',
            'password'  => 'SuperSecret123!',
            'tenant_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'access_token',
                    'refresh_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    #[Test]
    public function it_returns_401_on_invalid_credentials(): void
    {
        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('login')
            ->once()
            ->andThrow(new AuthenticationException());

        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'wrong@example.com',
            'password'  => 'WrongPassword!',
            'tenant_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_returns_403_for_inactive_account(): void
    {
        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('login')
            ->once()
            ->andThrow(new AccountInactiveException());

        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'disabled@example.com',
            'password'  => 'ValidPass123!',
            'tenant_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_fails_validation_when_email_is_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'password'  => 'ValidPass123!',
            'tenant_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_fails_validation_when_tenant_id_is_not_a_uuid(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'user@example.com',
            'password'  => 'ValidPass123!',
            'tenant_id' => 'not-a-uuid',
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_fails_validation_when_device_id_is_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'     => 'user@example.com',
            'password'  => 'ValidPass123!',
            'tenant_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        $response->assertStatus(422);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
