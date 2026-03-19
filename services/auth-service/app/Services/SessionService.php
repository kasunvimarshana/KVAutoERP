<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Contracts\Services\SessionServiceInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use App\Models\DeviceSession;
use Illuminate\Database\Eloquent\Collection;

class SessionService implements SessionServiceInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepository,
        private readonly TenantConfigServiceInterface $tenantConfigService,
    ) {}

    public function createSession(
        string $userId,
        string $tenantId,
        string $deviceId,
        string $deviceName,
        string $ipAddress,
        string $userAgent,
        string $hashedRefreshToken,
        \DateTimeInterface $refreshTokenExpiresAt,
    ): DeviceSession {
        $maxDevices = $this->tenantConfigService->getMaxDevicesPerUser($tenantId);

        // Enforce device limit — evict oldest session if needed
        $this->sessionRepository->deleteOldestIfOverLimit($userId, $maxDevices - 1);

        // Revoke any existing session for this device (re-login scenario)
        $existingSession = $this->sessionRepository->findByUserAndDevice($userId, $deviceId);
        if ($existingSession !== null) {
            $this->sessionRepository->revokeSession($existingSession->id);
        }

        return $this->sessionRepository->create([
            'user_id'                  => $userId,
            'tenant_id'                => $tenantId,
            'device_id'                => $deviceId,
            'device_name'              => $deviceName,
            'ip_address'               => $ipAddress,
            'user_agent'               => $userAgent,
            'refresh_token_hash'       => $hashedRefreshToken,
            'refresh_token_expires_at' => $refreshTokenExpiresAt,
            'last_activity_at'         => now(),
            'is_active'                => true,
        ]);
    }

    public function getActiveSessions(string $userId): Collection
    {
        return $this->sessionRepository->findActiveByUser($userId);
    }

    public function revokeSession(string $sessionId, string $userId): void
    {
        $session = $this->sessionRepository->findById($sessionId);

        if ($session === null || $session->user_id !== $userId) {
            return;
        }

        $this->sessionRepository->revokeSession($sessionId);
    }

    public function revokeAllSessions(string $userId): void
    {
        $this->sessionRepository->revokeAllUserSessions($userId);
    }

    public function revokeDeviceSession(string $userId, string $deviceId): void
    {
        $this->sessionRepository->revokeDeviceSession($userId, $deviceId);
    }

    public function touchSession(string $sessionId, string $ipAddress): void
    {
        $this->sessionRepository->updateLastActivity($sessionId, $ipAddress);
    }

    public function rotateRefreshToken(
        string $sessionId,
        string $newHashedRefreshToken,
        \DateTimeInterface $newExpiresAt,
    ): void {
        $this->sessionRepository->updateRefreshToken($sessionId, $newHashedRefreshToken, $newExpiresAt);
    }

    public function findByRefreshToken(string $rawRefreshToken): ?DeviceSession
    {
        $hash = hash('sha256', $rawRefreshToken);
        return $this->sessionRepository->findByToken($hash);
    }
}
