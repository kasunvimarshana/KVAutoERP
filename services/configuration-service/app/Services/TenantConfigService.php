<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\TenantConfigurationRepositoryInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use App\DTOs\TenantConfigurationDto;
use App\Exceptions\ConfigurationException;
use App\Models\TenantConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TenantConfigService implements TenantConfigServiceInterface
{
    public function __construct(
        private readonly TenantConfigurationRepositoryInterface $configRepository,
    ) {}

    public function getConfig(string $tenantId, string $serviceName, string $configKey): mixed
    {
        $cacheKey = "config:{$tenantId}:{$serviceName}:{$configKey}";
        $ttl = (int) config('configuration.config_cache_ttl', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($tenantId, $serviceName, $configKey) {
            $config = $this->configRepository->findByKey($tenantId, $serviceName, $configKey);

            return $config?->getTypedValue();
        });
    }

    public function getServiceConfig(string $tenantId, string $serviceName): array
    {
        $cacheKey = "config:{$tenantId}:{$serviceName}:all";
        $ttl = (int) config('configuration.config_cache_ttl', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($tenantId, $serviceName) {
            $configs = $this->configRepository->getAllActiveForService($tenantId, $serviceName);

            return $configs->mapWithKeys(
                fn (TenantConfiguration $config) => [$config->config_key => $config->getTypedValue()],
            )->toArray();
        });
    }

    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->configRepository->findByTenant($tenantId, $perPage);
    }

    public function create(TenantConfigurationDto $dto): TenantConfiguration
    {
        if ($this->configRepository->existsByKey($dto->tenantId, $dto->serviceName, $dto->configKey)) {
            throw new ConfigurationException(
                "Configuration key '{$dto->configKey}' already exists for service '{$dto->serviceName}'.",
                409,
            );
        }

        $config = $this->configRepository->create($dto->toArray());
        $this->flushServiceCache($dto->tenantId, $dto->serviceName);

        return $config;
    }

    public function update(string $id, TenantConfigurationDto $dto): TenantConfiguration
    {
        $existing = $this->findById($id);
        $config = $this->configRepository->update($id, $dto->toArray());
        $this->flushServiceCache($existing->tenant_id, $existing->service_name);

        return $config;
    }

    public function delete(string $id): void
    {
        $existing = $this->findById($id);
        $this->configRepository->delete($id);
        $this->flushServiceCache($existing->tenant_id, $existing->service_name);
    }

    public function upsert(TenantConfigurationDto $dto): TenantConfiguration
    {
        $config = $this->configRepository->upsert(
            $dto->tenantId,
            $dto->serviceName,
            $dto->configKey,
            $dto->toArray(),
        );
        $this->flushServiceCache($dto->tenantId, $dto->serviceName);

        return $config;
    }

    public function findById(string $id): TenantConfiguration
    {
        $config = $this->configRepository->findById($id);

        if ($config === null) {
            throw new ConfigurationException("Configuration not found with ID: {$id}", 404);
        }

        return $config;
    }

    private function flushServiceCache(string $tenantId, string $serviceName): void
    {
        Cache::forget("config:{$tenantId}:{$serviceName}:all");
    }
}
