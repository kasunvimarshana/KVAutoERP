<?php

declare(strict_types=1);

namespace App\Application\Contracts\Services;

use App\Domain\Models\Tenant;

/**
 * Tenant Configuration Service Contract
 * 
 * Provides dynamic runtime tenant configuration management.
 * All changes take effect immediately without redeployment.
 */
interface TenantConfigServiceInterface
{
    public function getTenant(string|int $tenantId): ?Tenant;
    public function getActiveTenant(string|int $tenantId): ?Tenant;
    public function get(string|int $tenantId, string $key, mixed $default = null): mixed;
    public function set(string|int $tenantId, string $key, mixed $value): bool;
    public function setFeatureFlag(string|int $tenantId, string $feature, bool $enabled): bool;
    public function hasFeature(string|int $tenantId, string $feature): bool;
    public function getAllActiveTenants();
}
