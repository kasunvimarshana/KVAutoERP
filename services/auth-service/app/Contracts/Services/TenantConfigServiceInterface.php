<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface TenantConfigServiceInterface
{
    /**
     * Return a tenant-specific configuration value, falling back to the platform default.
     */
    public function get(string $tenantId, string $key, mixed $default = null): mixed;

    /**
     * Persist a runtime configuration value for the tenant.
     */
    public function set(string $tenantId, string $key, mixed $value): void;

    /**
     * Check whether a feature flag is enabled for the tenant.
     */
    public function isFeatureEnabled(string $tenantId, string $feature): bool;

    /**
     * Enable a feature flag at runtime for the tenant.
     */
    public function enableFeature(string $tenantId, string $feature): void;

    /**
     * Disable a feature flag at runtime for the tenant.
     */
    public function disableFeature(string $tenantId, string $feature): void;

    /**
     * Return the JWT access token TTL (in minutes) configured for the tenant.
     */
    public function getAccessTokenTtl(string $tenantId): int;

    /**
     * Return the JWT refresh token TTL (in minutes) configured for the tenant.
     */
    public function getRefreshTokenTtl(string $tenantId): int;

    /**
     * Return the maximum number of devices a user may have active simultaneously.
     */
    public function getMaxDevicesPerUser(string $tenantId): int;

    /**
     * Flush all cached configuration for the tenant.
     */
    public function flushCache(string $tenantId): void;
}
