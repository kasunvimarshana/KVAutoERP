<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\RefreshTokenRepositoryInterface;
use App\Models\RefreshToken;

/**
 * Eloquent-backed refresh token repository.
 */
final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $data): RefreshToken
    {
        return RefreshToken::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function findValidByHash(string $tokenHash): ?RefreshToken
    {
        return RefreshToken::where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function revoke(string $tokenId): bool
    {
        return (bool) RefreshToken::where('id', $tokenId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllForUser(string $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeForDevice(string $userId, string $deviceId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * {@inheritDoc}
     */
    public function purgeExpiredAndRevoked(): int
    {
        return RefreshToken::where(function ($q): void {
            $q->whereNotNull('revoked_at')
              ->orWhere('expires_at', '<', now());
        })->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function countActiveDeviceSessions(string $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->distinct('device_id')
            ->count('device_id');
    }
}
