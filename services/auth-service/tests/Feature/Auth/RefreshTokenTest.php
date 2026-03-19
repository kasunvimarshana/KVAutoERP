<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Exceptions\InvalidRefreshTokenException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for POST /api/v1/auth/refresh.
 */
final class RefreshTokenTest extends TestCase
{
    #[Test]
    public function it_returns_new_token_pair_on_valid_refresh_token(): void
    {
        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('refreshTokens')
            ->once()
            ->andReturn([
                'access_token'  => 'new.access.token',
                'refresh_token' => 'new-refresh-token',
                'expires_in'    => 900,
                'token_type'    => 'Bearer',
            ]);

        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'valid-refresh-token-value',
            'device_id'     => 'test-device-001',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => ['access_token', 'refresh_token', 'expires_in', 'token_type'],
            ]);
    }

    #[Test]
    public function it_returns_401_on_invalid_refresh_token(): void
    {
        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('refreshTokens')
            ->once()
            ->andThrow(new InvalidRefreshTokenException());

        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'expired-or-revoked-token',
            'device_id'     => 'test-device-001',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_fails_validation_when_refresh_token_is_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/refresh', [
            'device_id' => 'test-device-001',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_fails_validation_when_device_id_is_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => 'some-token',
        ]);

        $response->assertStatus(422);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
