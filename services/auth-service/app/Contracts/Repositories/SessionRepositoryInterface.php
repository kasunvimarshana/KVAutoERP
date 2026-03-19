<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\DeviceSession;
use Illuminate\Database\Eloquent\Collection;

interface SessionRepositoryInterface
{
    public function create(array $data): DeviceSession;

    public function findById(string $id): ?DeviceSession;

    public function findByToken(string $refreshToken): ?DeviceSession;

    public function findActiveByUser(string $userId): Collection;

    public function findByUserAndDevice(string $userId, string $deviceId): ?DeviceSession;

    public function revokeSession(string $sessionId): void;

    public function revokeAllUserSessions(string $userId): void;

    public function revokeUserSessionsExcept(string $userId, string $sessionId): void;

    public function revokeDeviceSession(string $userId, string $deviceId): void;

    public function updateLastActivity(string $sessionId, string $ipAddress): void;

    public function updateRefreshToken(string $sessionId, string $hashedToken, \DateTimeInterface $expiresAt): void;

    public function cleanupExpired(): int;

    public function countActiveByUser(string $userId): int;

    public function deleteOldestIfOverLimit(string $userId, int $limit): void;
}
