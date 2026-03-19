<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;

/**
 * Contract for the core authentication service.
 *
 * Orchestrates login, logout, token refresh, and device-session
 * management by coordinating the token, revocation, and audit services.
 */
interface AuthServiceInterface
{
    /**
     * Authenticate a user by email and password, returning token pair.
     *
     * @param  string  $email      User email address.
     * @param  string  $password   Plain-text password.
     * @param  string  $tenantId   Tenant UUID.
     * @param  string  $deviceId   Caller's device identifier.
     * @param  string  $ipAddress  Request IP for audit logging.
     * @param  string  $userAgent  Request user-agent for audit logging.
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     *
     * @throws \App\Exceptions\AuthenticationException When credentials are invalid.
     * @throws \App\Exceptions\AccountInactiveException When account is disabled.
     */
    public function login(
        string $email,
        string $password,
        string $tenantId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
    ): array;

    /**
     * Revoke the current access token and its associated refresh token.
     *
     * @param  string  $accessToken   The current JWT access token.
     * @param  string  $ipAddress     Request IP for audit logging.
     * @param  string  $userAgent     Request user-agent.
     * @return bool
     */
    public function logout(string $accessToken, string $ipAddress, string $userAgent): bool;

    /**
     * Rotate a refresh token and issue a new access+refresh token pair.
     *
     * @param  string  $refreshToken  The current refresh token (raw).
     * @param  string  $deviceId      Device identifier.
     * @param  string  $ipAddress     Request IP.
     * @param  string  $userAgent     Request user-agent.
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     *
     * @throws \App\Exceptions\InvalidRefreshTokenException When the token is invalid or expired.
     */
    public function refreshTokens(
        string $refreshToken,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
    ): array;

    /**
     * Revoke all sessions for a user across every device (global logout).
     *
     * @param  string  $userId     User UUID.
     * @param  string  $ipAddress  Request IP.
     * @param  string  $userAgent  Request user-agent.
     * @return bool
     */
    public function revokeAllSessions(string $userId, string $ipAddress, string $userAgent): bool;

    /**
     * Revoke all sessions for a specific device belonging to a user.
     *
     * @param  string  $userId     User UUID.
     * @param  string  $deviceId   Device identifier to revoke.
     * @param  string  $ipAddress  Request IP.
     * @param  string  $userAgent  Request user-agent.
     * @return bool
     */
    public function revokeDeviceSession(
        string $userId,
        string $deviceId,
        string $ipAddress,
        string $userAgent,
    ): bool;

    /**
     * Register a new user within a tenant (admin operation).
     *
     * @param  array<string, mixed>  $data      User attributes.
     * @param  string                $tenantId  Tenant UUID.
     * @param  string                $actorId   UUID of the admin performing registration.
     * @return User
     */
    public function registerUser(array $data, string $tenantId, string $actorId): User;

    /**
     * Return the currently authenticated user model from JWT claims.
     *
     * @param  array<string, mixed>  $claims  Decoded JWT claims.
     * @return User|null
     */
    public function getUserFromClaims(array $claims): ?User;
}
