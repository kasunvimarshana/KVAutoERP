<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\ModuleRegistryDto;
use App\Models\ModuleRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ModuleRegistryServiceInterface
{
    /**
     * Check whether a module is enabled for a given tenant.
     */
    public function isModuleEnabled(string $tenantId, string $moduleKey): bool;

    /**
     * Retrieve all enabled modules for a tenant.
     */
    public function getEnabledModules(string $tenantId): Collection;

    /**
     * Paginated list of all modules for a tenant.
     */
    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Register a new module for a tenant.
     */
    public function create(ModuleRegistryDto $dto): ModuleRegistry;

    /**
     * Update an existing module registration.
     */
    public function update(string $id, ModuleRegistryDto $dto): ModuleRegistry;

    /**
     * Remove a module registration (soft-delete).
     */
    public function delete(string $id): void;

    /**
     * Toggle the enabled/disabled state of a module.
     * Validates that all dependent modules are still satisfied.
     */
    public function toggle(string $id): ModuleRegistry;

    /**
     * Find a module by its ID.
     */
    public function findById(string $id): ModuleRegistry;
}
