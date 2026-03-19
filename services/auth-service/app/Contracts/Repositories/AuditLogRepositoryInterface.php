<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;

    public function findByUser(string $userId, int $perPage = 50): LengthAwarePaginator;

    public function findByTenant(string $tenantId, int $perPage = 50): LengthAwarePaginator;

    public function findSuspiciousActivity(string $userId, int $windowMinutes = 15): array;

    public function countFailedLoginsByIp(string $ipAddress, int $windowMinutes = 15): int;

    public function countFailedLoginsByUser(string $userId, int $windowMinutes = 15): int;
}
