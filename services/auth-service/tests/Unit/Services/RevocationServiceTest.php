<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\RevocationService;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class RevocationServiceTest extends TestCase
{
    private RevocationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.ttl'         => 900,
            'jwt.refresh_ttl' => 2592000,
        ]);

        $this->service = new RevocationService();
    }

    // ──────────────────────────────────────────────────────────
    // revoke()
    // ──────────────────────────────────────────────────────────

    public function test_revoke_sets_redis_key_with_ttl(): void
    {
        Redis::shouldReceive('setex')
            ->once()
            ->with('revoked:test-jti', \Mockery::type('int'), '1');

        $this->service->revoke('test-jti', 900);

        $this->addToAssertionCount(1);
    }

    // ──────────────────────────────────────────────────────────
    // isRevoked()
    // ──────────────────────────────────────────────────────────

    public function test_is_revoked_returns_true_when_key_exists(): void
    {
        Redis::shouldReceive('exists')->once()->andReturn(1);

        $this->assertTrue($this->service->isRevoked('some-jti'));
    }

    public function test_is_revoked_returns_false_when_key_missing(): void
    {
        Redis::shouldReceive('exists')->once()->andReturn(0);

        $this->assertFalse($this->service->isRevoked('unknown-jti'));
    }

    // ──────────────────────────────────────────────────────────
    // getActiveDevices()
    // ──────────────────────────────────────────────────────────

    public function test_get_active_devices_returns_empty_array_when_no_data(): void
    {
        Redis::shouldReceive('get')->once()->andReturn(null);

        $devices = $this->service->getActiveDevices('user-1');

        $this->assertSame([], $devices);
    }

    public function test_get_active_devices_parses_stored_json(): void
    {
        $stored = json_encode([
            'device-a' => ['jti' => 'j1', 'refresh_token' => 'rt1', 'last_active' => 1700000000],
        ]);

        Redis::shouldReceive('get')->once()->andReturn($stored);

        $devices = $this->service->getActiveDevices('user-1');

        $this->assertArrayHasKey('device-a', $devices);
        $this->assertSame('j1', $devices['device-a']['jti']);
    }

    // ──────────────────────────────────────────────────────────
    // revokeAll()
    // ──────────────────────────────────────────────────────────

    public function test_revoke_all_blacklists_all_device_jtis(): void
    {
        $stored = json_encode([
            'device-a' => ['jti' => 'jti-a', 'refresh_token' => 'rt-a', 'last_active' => time()],
            'device-b' => ['jti' => 'jti-b', 'refresh_token' => 'rt-b', 'last_active' => time()],
        ]);

        Redis::shouldReceive('get')->once()->andReturn($stored);

        // Revoke each JTI (2 devices) + delete refresh tokens (2) + delete devices key (1)
        Redis::shouldReceive('setex')->twice(); // revoke jti-a and jti-b
        Redis::shouldReceive('del')->times(3);  // refresh rt-a, rt-b, then devices key

        $this->service->revokeAll('user-1');

        $this->addToAssertionCount(1);
    }

    // ──────────────────────────────────────────────────────────
    // revokeDevice()
    // ──────────────────────────────────────────────────────────

    public function test_revoke_device_removes_specific_device_and_blacklists_jti(): void
    {
        $stored = json_encode([
            'device-a' => ['jti' => 'jti-a', 'refresh_token' => 'rt-a', 'last_active' => time()],
            'device-b' => ['jti' => 'jti-b', 'refresh_token' => 'rt-b', 'last_active' => time()],
        ]);

        Redis::shouldReceive('get')->once()->andReturn($stored);
        Redis::shouldReceive('del')->once()->with('refresh:rt-a');
        Redis::shouldReceive('setex')->once()->with('revoked:jti-a', \Mockery::type('int'), '1');
        Redis::shouldReceive('setex')->once(); // update devices key

        $this->service->revokeDevice('user-1', 'device-a');

        $this->addToAssertionCount(1);
    }

    public function test_revoke_device_is_noop_for_unknown_device(): void
    {
        $stored = json_encode([
            'device-a' => ['jti' => 'jti-a', 'refresh_token' => 'rt-a', 'last_active' => time()],
        ]);

        Redis::shouldReceive('get')->once()->andReturn($stored);
        Redis::shouldReceive('del')->never();
        Redis::shouldReceive('setex')->never();

        $this->service->revokeDevice('user-1', 'nonexistent-device');

        $this->addToAssertionCount(1);
    }
}
