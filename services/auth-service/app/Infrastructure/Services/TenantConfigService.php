<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Contracts\Services\TenantConfigServiceInterface;
use App\Domain\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tenant Configuration Service Implementation
 * 
 * Manages dynamic tenant configurations that can be updated
 * at runtime WITHOUT restarting or redeploying the service.
 * 
 * Uses cache with tags for efficient invalidation.
 */
class TenantConfigService implements TenantConfigServiceInterface
{
    private const CACHE_PREFIX = 'tenant_config:';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get a tenant by its ID or slug (cached).
     * 
     * @param string|int $tenantId
     * @return Tenant|null
     */
    public function getTenant(string|int $tenantId): ?Tenant
    {
        return Cache::tags(['tenants', "tenant:{$tenantId}"])
            ->remember(
                self::CACHE_PREFIX . $tenantId,
                self::CACHE_TTL,
                fn() => Tenant::where('id', $tenantId)
                    ->orWhere('slug', $tenantId)
                    ->first()
            );
    }

    /**
     * Get an active tenant, returning null if inactive.
     * 
     * @param string|int $tenantId
     * @return Tenant|null
     */
    public function getActiveTenant(string|int $tenantId): ?Tenant
    {
        $tenant = $this->getTenant($tenantId);
        return ($tenant && $tenant->is_active) ? $tenant : null;
    }

    /**
     * Get a specific configuration value for the current tenant.
     * 
     * @param string|int $tenantId
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string|int $tenantId, string $key, mixed $default = null): mixed
    {
        $tenant = $this->getTenant($tenantId);
        return $tenant?->getSetting($key, $default) ?? $default;
    }

    /**
     * Dynamically update a tenant configuration at runtime.
     * Clears the cache so changes take effect immediately.
     * 
     * @param string|int $tenantId
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string|int $tenantId, string $key, mixed $value): bool
    {
        $tenant = $this->getTenant($tenantId);
        if (!$tenant) {
            return false;
        }

        $tenant->updateConfig($key, $value);
        
        // Invalidate all caches for this tenant
        $this->invalidateCache($tenantId);

        Log::info('Tenant config updated', [
            'tenant_id' => $tenantId,
            'key' => $key,
        ]);

        return true;
    }

    /**
     * Set a feature flag for a tenant at runtime.
     * 
     * @param string|int $tenantId
     * @param string $feature
     * @param bool $enabled
     * @return bool
     */
    public function setFeatureFlag(string|int $tenantId, string $feature, bool $enabled): bool
    {
        $tenant = $this->getTenant($tenantId);
        if (!$tenant) {
            return false;
        }

        $tenant->setFeatureFlag($feature, $enabled);
        $this->invalidateCache($tenantId);

        Log::info('Tenant feature flag updated', [
            'tenant_id' => $tenantId,
            'feature' => $feature,
            'enabled' => $enabled,
        ]);

        return true;
    }

    /**
     * Check if a feature is enabled for a tenant.
     * 
     * @param string|int $tenantId
     * @param string $feature
     * @return bool
     */
    public function hasFeature(string|int $tenantId, string $feature): bool
    {
        $tenant = $this->getTenant($tenantId);
        return $tenant?->hasFeature($feature) ?? false;
    }

    /**
     * Get all active tenants.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveTenants()
    {
        return Cache::tags(['tenants'])
            ->remember('all_active_tenants', self::CACHE_TTL, fn() => Tenant::where('is_active', true)->get());
    }

    /**
     * Invalidate all cached data for a tenant.
     * 
     * @param string|int $tenantId
     */
    private function invalidateCache(string|int $tenantId): void
    {
        try {
            Cache::tags(['tenants', "tenant:{$tenantId}"])->flush();
        } catch (\Exception $e) {
            // Fallback for cache drivers that don't support tags
            Cache::forget(self::CACHE_PREFIX . $tenantId);
            Cache::forget('all_active_tenants');
        }
    }
}
