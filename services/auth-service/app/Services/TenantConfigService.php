<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use Illuminate\Support\Facades\Cache;

class TenantConfigService implements TenantConfigServiceInterface
{
    private const CACHE_PREFIX = 'tenant_config:';
    private const CACHE_TTL    = 600; // 10 minutes

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    public function get(string $tenantId, string $key, mixed $default = null): mixed
    {
        $config = $this->loadTenantConfig($tenantId);
        return data_get($config, $key, $default);
    }

    public function set(string $tenantId, string $key, mixed $value): void
    {
        $this->tenantRepository->setConfiguration($tenantId, $key, $value);
        $this->flushCache($tenantId);
    }

    public function isFeatureEnabled(string $tenantId, string $feature): bool
    {
        return (bool) Cache::remember(
            self::CACHE_PREFIX . "{$tenantId}:feature:{$feature}",
            self::CACHE_TTL,
            fn () => $this->tenantRepository->getFeatureFlags($tenantId)[$feature]
                ?? config("tenant.default_features.{$feature}", false),
        );
    }

    public function enableFeature(string $tenantId, string $feature): void
    {
        $this->tenantRepository->setFeatureFlag($tenantId, $feature, true);
        $this->flushCache($tenantId);
    }

    public function disableFeature(string $tenantId, string $feature): void
    {
        $this->tenantRepository->setFeatureFlag($tenantId, $feature, false);
        $this->flushCache($tenantId);
    }

    public function getAccessTokenTtl(string $tenantId): int
    {
        return (int) $this->get($tenantId, 'token_lifetimes.access', config('jwt.ttl.access', 15));
    }

    public function getRefreshTokenTtl(string $tenantId): int
    {
        return (int) $this->get($tenantId, 'token_lifetimes.refresh', config('jwt.ttl.refresh', 43200));
    }

    public function getMaxDevicesPerUser(string $tenantId): int
    {
        return (int) $this->get($tenantId, 'max_devices_per_user', config('tenant.max_devices_per_user', 10));
    }

    public function flushCache(string $tenantId): void
    {
        Cache::forget(self::CACHE_PREFIX . $tenantId);

        foreach (array_keys(config('tenant.default_features', [])) as $feature) {
            Cache::forget(self::CACHE_PREFIX . "{$tenantId}:feature:{$feature}");
        }
    }

    private function loadTenantConfig(string $tenantId): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . $tenantId,
            self::CACHE_TTL,
            function () use ($tenantId) {
                $tenant = $this->tenantRepository->findById($tenantId);
                if ($tenant === null) {
                    return [];
                }

                return [
                    'feature_flags'       => $tenant->feature_flags ?? [],
                    'configurations'      => $tenant->configurations ?? [],
                    'token_lifetimes'     => $tenant->token_lifetimes ?? [],
                    'max_devices_per_user' => $tenant->getConfiguration('max_devices_per_user', config('tenant.max_devices_per_user', 10)),
                ];
            },
        );
    }
}
