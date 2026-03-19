<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TenantConfigurationRepositoryInterface;
use App\Models\TenantConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TenantConfigurationRepository implements TenantConfigurationRepositoryInterface
{
    public function findById(string $id): ?TenantConfiguration
    {
        return TenantConfiguration::find($id);
    }

    public function findByKey(string $tenantId, string $serviceName, string $configKey): ?TenantConfiguration
    {
        return TenantConfiguration::forTenant($tenantId)
            ->forService($serviceName)
            ->where('config_key', $configKey)
            ->first();
    }

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return TenantConfiguration::forTenant($tenantId)
            ->orderBy('service_name')
            ->orderBy('config_key')
            ->paginate($perPage);
    }

    public function findByService(string $tenantId, string $serviceName): Collection
    {
        return TenantConfiguration::forTenant($tenantId)
            ->forService($serviceName)
            ->orderBy('config_key')
            ->get();
    }

    public function create(array $data): TenantConfiguration
    {
        return TenantConfiguration::create($data);
    }

    public function update(string $id, array $data): TenantConfiguration
    {
        $config = TenantConfiguration::findOrFail($id);
        $config->update($data);

        return $config->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) TenantConfiguration::findOrFail($id)->delete();
    }

    public function upsert(string $tenantId, string $serviceName, string $configKey, array $data): TenantConfiguration
    {
        return TenantConfiguration::updateOrCreate(
            ['tenant_id' => $tenantId, 'service_name' => $serviceName, 'config_key' => $configKey],
            $data,
        );
    }

    public function existsByKey(string $tenantId, string $serviceName, string $configKey): bool
    {
        return TenantConfiguration::forTenant($tenantId)
            ->forService($serviceName)
            ->where('config_key', $configKey)
            ->exists();
    }

    public function getAllActiveForService(string $tenantId, string $serviceName): Collection
    {
        return TenantConfiguration::forTenant($tenantId)
            ->forService($serviceName)
            ->active()
            ->orderBy('config_key')
            ->get();
    }
}
