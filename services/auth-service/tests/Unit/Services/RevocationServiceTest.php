<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\RevocationService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface as RedisClient;

/**
 * Unit tests for RevocationService.
 */
final class RevocationServiceTest extends TestCase
{
    private MockInterface $redis;
    private RevocationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis   = Mockery::mock(RedisClient::class);
        $this->service = new RevocationService($this->redis, 'revoke');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_revokes_a_jti_with_correct_key_and_ttl(): void
    {
        $jti = 'test-jti-123';
        $ttl = 900;

        $this->redis->shouldReceive('set')
            ->once()
            ->with("revoke:jti:{$jti}", '1');

        $this->redis->shouldReceive('expire')
            ->once()
            ->with("revoke:jti:{$jti}", $ttl);

        $result = $this->service->revokeJti($jti, $ttl);

        self::assertTrue($result);
    }

    #[Test]
    public function it_returns_true_when_jti_is_in_redis(): void
    {
        $jti = 'revoked-jti';

        $this->redis->shouldReceive('exists')
            ->once()
            ->with("revoke:jti:{$jti}")
            ->andReturn(1);

        self::assertTrue($this->service->isJtiRevoked($jti));
    }

    #[Test]
    public function it_returns_false_when_jti_is_not_in_redis(): void
    {
        $jti = 'valid-jti';

        $this->redis->shouldReceive('exists')
            ->once()
            ->with("revoke:jti:{$jti}")
            ->andReturn(0);

        self::assertFalse($this->service->isJtiRevoked($jti));
    }

    #[Test]
    public function it_increments_user_token_version_on_revoke_all(): void
    {
        $userId = 'user-uuid-abc';

        $this->redis->shouldReceive('incr')
            ->once()
            ->with("revoke:user:{$userId}:version")
            ->andReturn(2);

        $this->redis->shouldReceive('expire')
            ->once()
            ->with("revoke:user:{$userId}:version", 2592000);

        $newVersion = $this->service->revokeAllForUser($userId);

        self::assertSame(2, $newVersion);
    }

    #[Test]
    public function it_returns_default_version_1_when_no_key_exists(): void
    {
        $userId = 'new-user-uuid';

        $this->redis->shouldReceive('get')
            ->once()
            ->with("revoke:user:{$userId}:version")
            ->andReturn(null);

        self::assertSame(1, $this->service->getUserTokenVersion($userId));
    }

    #[Test]
    public function it_returns_stored_version_when_key_exists(): void
    {
        $userId = 'user-with-version';

        $this->redis->shouldReceive('get')
            ->once()
            ->with("revoke:user:{$userId}:version")
            ->andReturn('3');

        self::assertSame(3, $this->service->getUserTokenVersion($userId));
    }

    #[Test]
    public function it_revokes_a_device_session(): void
    {
        $userId   = 'user-abc';
        $deviceId = 'device-xyz';

        $this->redis->shouldReceive('set')
            ->once()
            ->with("revoke:device:{$userId}:{$deviceId}", '1');

        $this->redis->shouldReceive('expire')
            ->once()
            ->with("revoke:device:{$userId}:{$deviceId}", 2592000);

        self::assertTrue($this->service->revokeForDevice($userId, $deviceId));
    }

    #[Test]
    public function it_detects_revoked_device(): void
    {
        $userId   = 'user-abc';
        $deviceId = 'device-xyz';

        $this->redis->shouldReceive('exists')
            ->once()
            ->with("revoke:device:{$userId}:{$deviceId}")
            ->andReturn(1);

        self::assertTrue($this->service->isDeviceRevoked($userId, $deviceId));
    }

    #[Test]
    public function it_detects_non_revoked_device(): void
    {
        $userId   = 'user-abc';
        $deviceId = 'device-not-revoked';

        $this->redis->shouldReceive('exists')
            ->once()
            ->with("revoke:device:{$userId}:{$deviceId}")
            ->andReturn(0);

        self::assertFalse($this->service->isDeviceRevoked($userId, $deviceId));
    }
}
