<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\ValueObjects\Password;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;

/**
 * Auth Domain Service.
 *
 * Encapsulates pure authentication logic: credential validation, token
 * lifecycle management, and token refresh.  No HTTP concerns here.
 */
final class AuthDomainService
{
    /** Access-token TTL in minutes (default 60). */
    private int $accessTokenTtl;

    /** Refresh-token TTL in days (default 30). */
    private int $refreshTokenTtl;

    public function __construct(
        int $accessTokenTtl = 60,
        int $refreshTokenTtl = 30,
    ) {
        $this->accessTokenTtl  = $accessTokenTtl;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Credential validation
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Verify that the supplied plaintext password matches the user's stored hash.
     */
    public function validateCredentials(User $user, string $password): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        /** @var \App\Infrastructure\Persistence\Models\User|null $model */
        $model = \App\Infrastructure\Persistence\Models\User::find($user->getId());

        if ($model === null) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check($password, $model->password);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Token generation
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Issue a new Passport personal access token pair for the given user.
     *
     * @return array{access_token: string, refresh_token: string|null, expires_in: int}
     */
    public function generateTokens(User $user): array
    {
        /** @var \App\Infrastructure\Persistence\Models\User $model */
        $model = \App\Infrastructure\Persistence\Models\User::findOrFail($user->getId());

        /** @var PersonalAccessTokenResult $tokenResult */
        $tokenResult = $model->createToken(
            name: 'auth-service-token',
            expiresAt: now()->addMinutes($this->accessTokenTtl),
        );

        return [
            'access_token'  => $tokenResult->accessToken,
            'refresh_token' => $this->generateRefreshToken($user->getId()),
            'expires_in'    => $this->accessTokenTtl * 60,
        ];
    }

    /**
     * Revoke all active tokens for the given user.
     */
    public function revokeTokens(string $userId): void
    {
        /** @var \App\Infrastructure\Persistence\Models\User|null $model */
        $model = \App\Infrastructure\Persistence\Models\User::find($userId);

        if ($model === null) {
            return;
        }

        // Revoke all Passport tokens.
        $model->tokens()->each(function (Token $token): void {
            $token->revoke();
        });

        // Remove any stored refresh tokens from the cache.
        Cache::forget($this->refreshTokenCacheKey($userId));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Token refresh
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Exchange a valid refresh token for a fresh access + refresh token pair.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     *
     * @throws \RuntimeException  When the refresh token is invalid or expired.
     */
    public function refreshToken(string $refreshToken): array
    {
        $userId = $this->validateRefreshToken($refreshToken);

        if ($userId === null) {
            throw new \RuntimeException('Invalid or expired refresh token.');
        }

        /** @var \App\Infrastructure\Persistence\Models\User $model */
        $model = \App\Infrastructure\Persistence\Models\User::findOrFail($userId);

        // Revoke the old access tokens before issuing new ones.
        $model->tokens()->each(function (Token $token): void {
            $token->revoke();
        });

        /** @var PersonalAccessTokenResult $tokenResult */
        $tokenResult = $model->createToken(
            name: 'auth-service-token',
            expiresAt: now()->addMinutes($this->accessTokenTtl),
        );

        $newRefreshToken = $this->generateRefreshToken($userId);

        return [
            'access_token'  => $tokenResult->accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in'    => $this->accessTokenTtl * 60,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Generate a cryptographically secure refresh token, caching both a
     * user-keyed entry and a reverse hash-keyed entry for fast lookup.
     */
    private function generateRefreshToken(string $userId): string
    {
        $token  = bin2hex(random_bytes(64));
        $hashed = hash('sha256', $token);
        $ttl    = now()->addDays($this->refreshTokenTtl);

        // Forward lookup: user → hash
        Cache::put(
            key: $this->refreshTokenCacheKey($userId),
            value: $hashed,
            ttl: $ttl,
        );

        // Reverse lookup: hash → userId  (used by validateRefreshToken)
        Cache::put(
            key: 'refresh_token:hash:' . $hashed,
            value: $userId,
            ttl: $ttl,
        );

        return $token;
    }

    /**
     * Validate a refresh token and return the associated user ID, or null.
     */
    private function validateRefreshToken(string $refreshToken): ?string
    {
        $hashed = hash('sha256', $refreshToken);

        // Resolve userId via the reverse-lookup entry.
        $userId = Cache::get('refresh_token:hash:' . $hashed);

        if ($userId === null) {
            return null;
        }

        // Double-check the forward lookup is still consistent.
        $stored = Cache::get($this->refreshTokenCacheKey((string) $userId));

        if ($stored !== $hashed) {
            return null;
        }

        return (string) $userId;
    }

    /**
     * Build the per-user refresh token cache key.
     */
    private function refreshTokenCacheKey(string $userId): string
    {
        return 'refresh_token:user:' . $userId;
    }
}
