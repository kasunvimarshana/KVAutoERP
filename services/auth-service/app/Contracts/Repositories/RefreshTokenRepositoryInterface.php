<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\RefreshToken;

/**
 * Contract for refresh-token persistence operations.
 */
interface RefreshTokenRepositoryInterface
{
    /**
     * Store a new refresh token.
     *
     * @param  array<string, mixed>  $data  Token attributes.
     * @return RefreshToken
     */
    public function create(array $data): RefreshToken;

    /**
     * Find a valid (non-revoked, non-expired) refresh token by its hash.
     *
     * @param  string  $tokenHash  SHA-256 hash of the raw token.
     * @return RefreshToken|null
     */
    public function findValidByHash(string $tokenHash): ?RefreshToken;

    /**
     * Revoke a single refresh token by its ID.
     *
     * @param  string  $tokenId  Token UUID.
     * @return bool
     */
    public function revoke(string $tokenId): bool;

    /**
     * Revoke all refresh tokens for a user (global logout).
     *
     * @param  string  $userId  User UUID.
     * @return int  Number of tokens revoked.
     */
    public function revokeAllForUser(string $userId): int;

    /**
     * Revoke all refresh tokens for a user+device pair.
     *
     * @param  string  $userId    User UUID.
     * @param  string  $deviceId  Device identifier.
     * @return int  Number of tokens revoked.
     */
    public function revokeForDevice(string $userId, string $deviceId): int;

    /**
     * Delete all expired or revoked refresh tokens (garbage collection).
     *
     * @return int  Number of tokens deleted.
     */
    public function purgeExpiredAndRevoked(): int;

    /**
     * Count active (non-revoked, non-expired) device sessions for a user.
     *
     * @param  string  $userId  User UUID.
     * @return int
     */
    public function countActiveDeviceSessions(string $userId): int;
}
