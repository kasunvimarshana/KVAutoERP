<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\TenantConfigurationDto;
use App\Models\TenantConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TenantConfigServiceInterface
{
    /**
     * Retrieve a single configuration value for the given tenant and service.
     */
    public function getConfig(string $tenantId, string $serviceName, string $configKey): mixed;

    /**
     * Retrieve all active configurations for a tenant's service as a key→value map.
     */
    public function getServiceConfig(string $tenantId, string $serviceName): array;

    /**
     * Paginated list of all configurations for a tenant.
     */
    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new tenant configuration entry.
     */
    public function create(TenantConfigurationDto $dto): TenantConfiguration;

    /**
     * Update an existing configuration entry.
     */
    public function update(string $id, TenantConfigurationDto $dto): TenantConfiguration;

    /**
     * Delete a configuration entry (soft-delete).
     */
    public function delete(string $id): void;

    /**
     * Upsert — create or update based on (tenant_id, service_name, config_key).
     */
    public function upsert(TenantConfigurationDto $dto): TenantConfiguration;

    /**
     * Find a configuration by its ID.
     */
    public function findById(string $id): TenantConfiguration;
}
