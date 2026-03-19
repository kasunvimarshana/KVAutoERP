<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Models\DeviceSession;
use Illuminate\Database\Eloquent\Collection;

class SessionRepository implements SessionRepositoryInterface
{
    public function create(array $data): DeviceSession
    {
        return DeviceSession::create($data);
    }

    public function findById(string $id): ?DeviceSession
    {
        return DeviceSession::find($id);
    }

    public function findByToken(string $refreshToken): ?DeviceSession
    {
        return DeviceSession::active()
            ->where('refresh_token_hash', $refreshToken)
            ->first();
    }

    public function findActiveByUser(string $userId): Collection
    {
        return DeviceSession::active()->forUser($userId)->get();
    }

    public function findByUserAndDevice(string $userId, string $deviceId): ?DeviceSession
    {
        return DeviceSession::forUser($userId)
            ->where('device_id', $deviceId)
            ->first();
    }

    public function revokeSession(string $sessionId): void
    {
        DeviceSession::where('id', $sessionId)->update([
            'is_active'         => false,
            'revoked_at'        => now(),
            'revocation_reason' => 'manual',
        ]);
    }

    public function revokeAllUserSessions(string $userId): void
    {
        DeviceSession::forUser($userId)->active()->update([
            'is_active'         => false,
            'revoked_at'        => now(),
            'revocation_reason' => 'global_logout',
        ]);
    }

    public function revokeUserSessionsExcept(string $userId, string $sessionId): void
    {
        DeviceSession::forUser($userId)
            ->active()
            ->where('id', '!=', $sessionId)
            ->update([
                'is_active'         => false,
                'revoked_at'        => now(),
                'revocation_reason' => 'logout_other_devices',
            ]);
    }

    public function revokeDeviceSession(string $userId, string $deviceId): void
    {
        DeviceSession::forUser($userId)
            ->where('device_id', $deviceId)
            ->update([
                'is_active'         => false,
                'revoked_at'        => now(),
                'revocation_reason' => 'device_logout',
            ]);
    }

    public function updateLastActivity(string $sessionId, string $ipAddress): void
    {
        DeviceSession::where('id', $sessionId)->update([
            'last_activity_at' => now(),
            'ip_address'       => $ipAddress,
        ]);
    }

    public function updateRefreshToken(string $sessionId, string $hashedToken, \DateTimeInterface $expiresAt): void
    {
        DeviceSession::where('id', $sessionId)->update([
            'refresh_token_hash'       => $hashedToken,
            'refresh_token_expires_at' => $expiresAt,
            'last_activity_at'         => now(),
        ]);
    }

    public function cleanupExpired(): int
    {
        return DeviceSession::where('refresh_token_expires_at', '<', now())->delete();
    }

    public function countActiveByUser(string $userId): int
    {
        return DeviceSession::active()->forUser($userId)->count();
    }

    public function deleteOldestIfOverLimit(string $userId, int $limit): void
    {
        $activeCount = $this->countActiveByUser($userId);

        if ($activeCount >= $limit) {
            $toDelete = $activeCount - $limit + 1;

            DeviceSession::active()
                ->forUser($userId)
                ->orderBy('last_activity_at', 'asc')
                ->limit($toDelete)
                ->get()
                ->each(fn ($s) => $this->revokeSession($s->id));
        }
    }
}
