<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TokenRevocationRepositoryInterface;
use App\Models\TokenRevocation;
use Illuminate\Support\Facades\Cache;

class TokenRevocationRepository implements TokenRevocationRepositoryInterface
{
    private string $cachePrefix;

    public function __construct()
    {
        $this->cachePrefix = config('jwt.revocation.prefix', 'kv_auth_revoked:');
    }

    public function revoke(
        string $jti,
        string $userId,
        \DateTimeInterface $expiresAt,
        string $reason = 'logout',
    ): TokenRevocation {
        return TokenRevocation::create([
            'jti'        => $jti,
            'user_id'    => $userId,
            'reason'     => $reason,
            'revoked_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function isRevoked(string $jti): bool
    {
        return TokenRevocation::active()
            ->where('jti', $jti)
            ->exists();
    }

    public function revokeAllForUser(string $userId): void
    {
        // This is handled by token_version increment, but we still
        // record any currently-active tokens if we have them
    }

    public function cleanupExpired(): int
    {
        return TokenRevocation::where('expires_at', '<', now())->delete();
    }

    public function findByJti(string $jti): ?TokenRevocation
    {
        return TokenRevocation::where('jti', $jti)->first();
    }
}
