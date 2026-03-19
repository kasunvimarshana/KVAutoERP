<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\TokenClaimsDto;
use App\DTOs\TokenPairDto;

interface TokenServiceInterface
{
    /**
     * Issue a signed JWT access token with tenant-aware claims.
     */
    public function issueAccessToken(TokenClaimsDto $claims): string;

    /**
     * Issue an opaque refresh token, store its hash in the session store.
     */
    public function issueRefreshToken(string $userId, string $sessionId): string;

    /**
     * Decode and verify a JWT access token locally (no remote call).
     * Throws TokenException on failure.
     */
    public function decodeAccessToken(string $token): array;

    /**
     * Verify a raw refresh token against its stored hash.
     */
    public function verifyRefreshToken(string $rawRefreshToken, string $hashedToken): bool;

    /**
     * Hash a refresh token for secure storage.
     */
    public function hashRefreshToken(string $rawRefreshToken): string;

    /**
     * Issue a short-lived service-to-service JWT.
     */
    public function issueServiceToken(string $serviceId, string $tenantId): string;

    /**
     * Verify a service-to-service JWT.
     */
    public function verifyServiceToken(string $token): array;

    /**
     * Revoke a specific token by its JTI (JWT ID).
     */
    public function revokeByJti(string $jti, string $userId, int $expiresInSeconds, string $reason = 'logout'): void;

    /**
     * Check whether a JTI has been revoked.
     */
    public function isRevoked(string $jti): bool;

    /**
     * Get the token's remaining TTL in seconds.
     */
    public function getRemainingTtl(array $payload): int;

    /**
     * Issue a signed URL token (for file access etc.).
     */
    public function issueSignedUrlToken(string $url, string $userId, int $ttlSeconds): string;

    /**
     * Verify a signed URL token.
     */
    public function verifySignedUrlToken(string $token, string $url): bool;
}
