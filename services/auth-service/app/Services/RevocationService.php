<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\RevocationServiceInterface;
use Predis\ClientInterface as RedisClient;

/**
 * Redis-backed distributed token revocation service.
 *
 * Uses three mechanisms:
 *   1. JTI blocklist — Redis SET per token (key: `{prefix}:jti:{jti}`).
 *   2. User token version — Redis counter (key: `{prefix}:user:{userId}:version`).
 *   3. Device revocation — Redis SET (key: `{prefix}:device:{userId}:{deviceId}`).
 */
final class RevocationService implements RevocationServiceInterface
{
    public function __construct(
        private readonly RedisClient $redis,
        private readonly string $prefix = 'revoke',
    ) {}

    /**
     * {@inheritDoc}
     */
    public function revokeJti(string $jti, int $ttl): bool
    {
        $key = $this->jtiKey($jti);

        $this->redis->set($key, '1');
        $this->redis->expire($key, $ttl);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllForUser(string $userId): int
    {
        $key = $this->userVersionKey($userId);

        /** @var int $newVersion */
        $newVersion = $this->redis->incr($key);

        // Keep the version key alive for 30 days (beyond any token lifetime).
        $this->redis->expire($key, 2592000);

        return $newVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeForDevice(string $userId, string $deviceId): bool
    {
        $key = $this->deviceKey($userId, $deviceId);

        $this->redis->set($key, '1');
        // Persist device revocation for 30 days.
        $this->redis->expire($key, 2592000);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isJtiRevoked(string $jti): bool
    {
        return (bool) $this->redis->exists($this->jtiKey($jti));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserTokenVersion(string $userId): int
    {
        $version = $this->redis->get($this->userVersionKey($userId));

        return $version !== null ? (int) $version : 1;
    }

    /**
     * {@inheritDoc}
     */
    public function isDeviceRevoked(string $userId, string $deviceId): bool
    {
        return (bool) $this->redis->exists($this->deviceKey($userId, $deviceId));
    }

    // -------------------------------------------------------------------------
    // Private key helpers
    // -------------------------------------------------------------------------

    private function jtiKey(string $jti): string
    {
        return "{$this->prefix}:jti:{$jti}";
    }

    private function userVersionKey(string $userId): string
    {
        return "{$this->prefix}:user:{$userId}:version";
    }

    private function deviceKey(string $userId, string $deviceId): string
    {
        return "{$this->prefix}:device:{$userId}:{$deviceId}";
    }
}
