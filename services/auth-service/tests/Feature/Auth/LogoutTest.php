<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Services\AuthContext;
use KvEnterprise\SharedKernel\Contracts\Auth\TokenServiceInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for POST /api/v1/auth/logout.
 */
final class LogoutTest extends TestCase
{
    #[Test]
    public function it_logs_out_successfully_with_valid_jwt(): void
    {
        // Arrange: mock the token service to pass the jwt.verify middleware.
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldReceive('verify')->once()->andReturn(true);
        $tokenService->shouldReceive('decode')->andReturn([
            'user_id'       => 'user-uuid-123',
            'tenant_id'     => 'tenant-uuid-456',
            'device_id'     => 'device-abc',
            'jti'           => 'jti-test-001',
            'token_version' => 1,
        ]);

        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('logout')->once()->andReturn(true);

        $this->app->instance(TokenServiceInterface::class, $tokenService);
        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson(
            '/api/v1/auth/logout',
            [],
            ['Authorization' => 'Bearer valid.jwt.token'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    #[Test]
    public function it_returns_401_when_no_token_provided(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_returns_401_when_token_is_invalid(): void
    {
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldReceive('verify')->once()->andReturn(false);

        $this->app->instance(TokenServiceInterface::class, $tokenService);

        $response = $this->postJson(
            '/api/v1/auth/logout',
            [],
            ['Authorization' => 'Bearer invalid.token.here'],
        );

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_revokes_all_sessions_on_revoke_all_endpoint(): void
    {
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $tokenService->shouldReceive('verify')->once()->andReturn(true);
        $tokenService->shouldReceive('decode')->andReturn([
            'user_id'       => 'user-uuid-123',
            'tenant_id'     => 'tenant-uuid-456',
            'device_id'     => 'device-abc',
            'jti'           => 'jti-test-002',
            'token_version' => 1,
        ]);

        $authService = Mockery::mock(AuthServiceInterface::class);
        $authService->shouldReceive('revokeAllSessions')->once()->andReturn(true);

        $this->app->instance(TokenServiceInterface::class, $tokenService);
        $this->app->instance(AuthServiceInterface::class, $authService);

        $response = $this->postJson(
            '/api/v1/auth/revoke-all',
            [],
            ['Authorization' => 'Bearer valid.jwt.token'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
