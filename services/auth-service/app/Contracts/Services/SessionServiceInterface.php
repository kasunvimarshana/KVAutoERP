<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\DeviceSession;
use Illuminate\Database\Eloquent\Collection;

interface SessionServiceInterface
{
    /**
     * Create a new device session and enforce the per-user device limit.
     */
    public function createSession(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $deviceName,
        string $ipAddress,
        string $userAgent,
        string $hashedRefreshToken,
        \DateTimeInterface $refreshTokenExpiresAt,
    ): DeviceSession;

    /**
     * Retrieve all active sessions for a user.
     */
    public function getActiveSessions(string $userId): Collection;

    /**
     * Revoke a specific session by its ID.
     */
    public function revokeSession(string $sessionId, string $userId): void;

    /**
     * Revoke all sessions for a user (global logout).
     */
    public function revokeAllSessions(string $userId): void;

    /**
     * Revoke the session associated with a specific device.
     */
    public function revokeDeviceSession(string $userId, string $deviceId): void;

    /**
     * Record activity on a session (last seen IP, timestamp).
     */
    public function touchSession(string $sessionId, string $ipAddress): void;

    /**
     * Rotate the refresh token stored in the session.
     */
    public function rotateRefreshToken(
        string $sessionId,
        string $newHashedRefreshToken,
        \DateTimeInterface $newExpiresAt,
    ): void;

    /**
     * Find the session associated with a given refresh token hash.
     */
    public function findByRefreshToken(string $rawRefreshToken): ?DeviceSession;
}
