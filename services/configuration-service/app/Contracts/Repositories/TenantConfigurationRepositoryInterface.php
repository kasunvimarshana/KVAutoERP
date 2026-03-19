<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\TenantConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TenantConfigurationRepositoryInterface
{
    public function findById(string $id): ?TenantConfiguration;

    public function findByKey(string $tenantId, string $serviceName, string $configKey): ?TenantConfiguration;

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function findByService(string $tenantId, string $serviceName): Collection;

    public function create(array $data): TenantConfiguration;

    public function update(string $id, array $data): TenantConfiguration;

    public function delete(string $id): bool;

    public function upsert(string $tenantId, string $serviceName, string $configKey, array $data): TenantConfiguration;

    public function existsByKey(string $tenantId, string $serviceName, string $configKey): bool;

    public function getAllActiveForService(string $tenantId, string $serviceName): Collection;
}
