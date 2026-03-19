<?php

declare(strict_types=1);

namespace App\Contracts\Services;

/**
 * Contract for the distributed token revocation service.
 *
 * Backed by Redis, this service maintains two revocation mechanisms:
 *   1. JTI blocklist — individual token revocation (logout, device logout).
 *   2. Token version — bulk invalidation via incrementing version number.
 */
interface RevocationServiceInterface
{
    /**
     * Add a JTI to the Redis revocation set with a TTL matching the token's
     * remaining lifetime, so the entry self-expires.
     *
     * @param  string  $jti  The JWT ID claim.
     * @param  int     $ttl  Seconds until the entry expires (≥ token's remaining TTL).
     * @return bool
     */
    public function revokeJti(string $jti, int $ttl): bool;

    /**
     * Increment the user's token version in Redis, instantly invalidating
     * every access token issued with an older version number.
     *
     * @param  string  $userId  User UUID.
     * @return int  The new (incremented) version value.
     */
    public function revokeAllForUser(string $userId): int;

    /**
     * Mark all tokens for a user+device pair as revoked.
     *
     * Adds the device pair to a Redis set. The JWT middleware checks this
     * set when processing requests from that device.
     *
     * @param  string  $userId    User UUID.
     * @param  string  $deviceId  Device identifier.
     * @return bool
     */
    public function revokeForDevice(string $userId, string $deviceId): bool;

    /**
     * Check whether a JTI is present in the revocation set.
     *
     * @param  string  $jti  The JWT ID claim.
     * @return bool
     */
    public function isJtiRevoked(string $jti): bool;

    /**
     * Retrieve the current token version for a user from Redis.
     *
     * Returns 1 (the initial version) if no version key exists yet.
     *
     * @param  string  $userId  User UUID.
     * @return int
     */
    public function getUserTokenVersion(string $userId): int;

    /**
     * Check whether a device session has been explicitly revoked.
     *
     * @param  string  $userId    User UUID.
     * @param  string  $deviceId  Device identifier.
     * @return bool
     */
    public function isDeviceRevoked(string $userId, string $deviceId): bool;
}
