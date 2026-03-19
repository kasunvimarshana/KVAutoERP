<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function findByUser(string $userId, int $perPage = 50): LengthAwarePaginator
    {
        return AuditLog::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByTenant(string $tenantId, int $perPage = 50): LengthAwarePaginator
    {
        return AuditLog::forTenant($tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findSuspiciousActivity(string $userId, int $windowMinutes = 15): array
    {
        return AuditLog::forUser($userId)
            ->where('severity', 'critical')
            ->withinWindow($windowMinutes)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function countFailedLoginsByIp(string $ipAddress, int $windowMinutes = 15): int
    {
        return AuditLog::forEvent('auth.login_failed')
            ->where('ip_address', $ipAddress)
            ->withinWindow($windowMinutes)
            ->count();
    }

    public function countFailedLoginsByUser(string $userId, int $windowMinutes = 15): int
    {
        return AuditLog::forUser($userId)
            ->forEvent('auth.login_failed')
            ->withinWindow($windowMinutes)
            ->count();
    }
}
