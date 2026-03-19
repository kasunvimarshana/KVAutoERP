<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Auth;

/**
 * Low-level contract for RS256 JWT issuance, verification, and revocation.
 */
interface TokenServiceInterface
{
    /**
     * Sign and issue a JWT with the given claims and TTL (seconds).
     */
    public function issue(array $claims, int $ttl): string;

    /**
     * Issue an opaque refresh token persisted in Redis.
     */
    public function issueRefreshToken(string $userId, string $deviceId, string $jti): string;

    /**
     * Verify the signature, expiry, and revocation status of an access token.
     *
     * @return array<string, mixed> Decoded claims
     * @throws \RuntimeException on verification failure
     */
    public function verify(string $token): array;

    /**
     * Decode a token without signature verification (useful for logging).
     *
     * @return array<string, mixed>
     */
    public function decode(string $token, bool $verify = true): array;

    /**
     * Add a JTI to the Redis revocation list.
     */
    public function revoke(string $jti): void;

    /**
     * Return true when the JTI is in the revocation list.
     */
    public function isRevoked(string $jti): bool;

    /**
     * Return the PEM-encoded RSA public key for downstream token verification.
     */
    public function getPublicKey(): string;

    /**
     * Build the standard tenant-aware claims array for a user.
     */
    public function buildClaims(array $user, string $deviceId, string $tenantId): array;
}
