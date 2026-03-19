<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\RevocationServiceContract;
use App\Contracts\TokenServiceContract;
use Tests\TestCase;

/**
 * Feature tests for SessionController (active device / session management).
 *
 * The JWT middleware is bypassed by mocking TokenServiceContract so that
 * tests can exercise the session endpoints without needing real keys or Redis.
 */
class SessionControllerTest extends TestCase
{
    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function mockTokenService(string $userId = 'user-1', string $tenantId = 'tenant-1'): void
    {
        $this->mock(TokenServiceContract::class)
            ->shouldReceive('verify')
            ->andReturn([
                'sub'           => $userId,
                'jti'           => 'test-jti',
                'tenant_id'     => $tenantId,
                'roles'         => ['admin'],
                'permissions'   => [],
                'device_id'     => 'device-1',
                'token_version' => 1,
            ]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/auth/sessions
    // ──────────────────────────────────────────────────────────

    public function test_devices_returns_active_sessions(): void
    {
        $this->mockTokenService();

        $devices = [
            'device-1' => ['jti' => 'j1', 'last_active' => 1700000000],
            'device-2' => ['jti' => 'j2', 'last_active' => 1700000100],
        ];

        $this->mock(RevocationServiceContract::class)
            ->shouldReceive('getActiveDevices')
            ->once()
            ->with('user-1')
            ->andReturn($devices);

        $this->withHeaders(['Authorization' => 'Bearer test-token'])
            ->getJson('/api/v1/auth/sessions')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_devices_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/auth/sessions')
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/auth/sessions/{deviceId}
    // ──────────────────────────────────────────────────────────

    public function test_revoke_device_session(): void
    {
        $this->mockTokenService();

        $this->mock(RevocationServiceContract::class)
            ->shouldReceive('revokeDevice')
            ->once()
            ->with('user-1', 'device-abc');

        $this->withHeaders(['Authorization' => 'Bearer test-token'])
            ->deleteJson('/api/v1/auth/sessions/device-abc')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Device session revoked');
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/auth/sessions (revoke all)
    // ──────────────────────────────────────────────────────────

    public function test_revoke_all_sessions(): void
    {
        $this->mockTokenService();

        $this->mock(RevocationServiceContract::class)
            ->shouldReceive('revokeAll')
            ->once()
            ->with('user-1');

        $this->withHeaders(['Authorization' => 'Bearer test-token'])
            ->deleteJson('/api/v1/auth/sessions')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'All sessions revoked');
    }
}
