<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\AuthResultDto;
use App\DTOs\LoginCredentialsDto;
use App\DTOs\LogoutContextDto;
use App\DTOs\TokenPairDto;

interface AuthServiceInterface
{
    /**
     * Authenticate a user with the given credentials.
     * Issues an access + refresh token pair on success.
     */
    public function login(LoginCredentialsDto $credentials): AuthResultDto;

    /**
     * Log out the current device session and revoke the access token.
     */
    public function logout(LogoutContextDto $context): void;

    /**
     * Revoke all active sessions for the user across all devices (global logout).
     */
    public function logoutAllDevices(string $userId, string $tenantId): void;

    /**
     * Revoke the session for a specific device.
     */
    public function logoutDevice(string $userId, string $deviceId, string $tenantId): void;

    /**
     * Exchange a valid refresh token for a new access + refresh token pair.
     */
    public function refreshTokens(string $refreshToken, string $deviceId): TokenPairDto;

    /**
     * Validate an access token's signature, claims, and revocation status.
     * Returns the decoded payload on success.
     */
    public function validateAccessToken(string $accessToken): array;

    /**
     * Register a new user under the given tenant.
     */
    public function register(array $userData, string $tenantId): AuthResultDto;

    /**
     * Change the user's password and rotate token version to invalidate existing tokens.
     */
    public function changePassword(string $userId, string $currentPassword, string $newPassword): void;

    /**
     * Initiate a password reset flow.
     */
    public function initiatePasswordReset(string $email, string $tenantId): void;

    /**
     * Complete password reset using a valid signed reset token.
     */
    public function completePasswordReset(string $resetToken, string $newPassword): void;
}
