<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Auth;

/**
 * Contract for JWT token lifecycle management.
 *
 * The Auth microservice provides the concrete implementation; all other
 * services rely on this interface for local token verification using
 * the public key, avoiding synchronous calls to the Auth service.
 */
interface TokenServiceInterface
{
    /**
     * Issue a new signed JWT access token for the given payload.
     *
     * @param  array<string, mixed>  $claims    Token claims (user_id, tenant_id, roles, …).
     * @param  int|null              $ttl       Optional TTL override in seconds.
     * @return string                            Signed JWT string.
     */
    public function issue(array $claims, ?int $ttl = null): string;

    /**
     * Verify a JWT token's signature and expiry.
     *
     * Performs local verification using the public key – no network call.
     *
     * @param  string  $token  The raw JWT string.
     * @return bool             True if the token is valid and not revoked.
     */
    public function verify(string $token): bool;

    /**
     * Refresh an access token using a valid refresh token.
     *
     * Issues a new access token and rotates the refresh token.
     *
     * @param  string  $refreshToken  The current refresh token.
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function refresh(string $refreshToken): array;

    /**
     * Revoke a token so it can no longer be used.
     *
     * Adds the token's JTI to the distributed revocation list (Redis).
     *
     * @param  string  $token  The JWT string to revoke.
     * @return bool             True if successfully revoked.
     */
    public function revoke(string $token): bool;

    /**
     * Revoke all tokens issued to a specific user (global logout).
     *
     * Increments the user's token_version in the revocation store so
     * all previously issued tokens become invalid immediately.
     *
     * @param  string  $userId  The user's unique identifier.
     * @return bool              True if successfully revoked.
     */
    public function revokeAllForUser(string $userId): bool;

    /**
     * Revoke all tokens associated with a specific device session.
     *
     * @param  string  $userId    The user's unique identifier.
     * @param  string  $deviceId  The device session identifier.
     * @return bool                True if successfully revoked.
     */
    public function revokeForDevice(string $userId, string $deviceId): bool;

    /**
     * Decode a JWT token and return its claims without full verification.
     *
     * Use only for non-security-critical inspection (e.g., logging).
     * Always use {@see verify()} before trusting decoded claims.
     *
     * @param  string  $token  The raw JWT string.
     * @return array<string, mixed>  The decoded payload claims.
     */
    public function decode(string $token): array;

    /**
     * Check whether a token's JTI appears in the revocation list.
     *
     * @param  string  $jti  The JWT ID claim value.
     * @return bool            True if the token has been revoked.
     */
    public function isRevoked(string $jti): bool;
}
