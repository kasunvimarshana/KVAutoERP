<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ModuleRegistryRepositoryInterface;
use App\Contracts\Services\ModuleRegistryServiceInterface;
use App\DTOs\ModuleRegistryDto;
use App\Exceptions\ConfigurationException;
use App\Models\ModuleRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ModuleRegistryService implements ModuleRegistryServiceInterface
{
    public function __construct(
        private readonly ModuleRegistryRepositoryInterface $moduleRepository,
    ) {}

    public function isModuleEnabled(string $tenantId, string $moduleKey): bool
    {
        $module = $this->moduleRepository->findByKey($tenantId, $moduleKey);

        return $module?->is_enabled ?? false;
    }

    public function getEnabledModules(string $tenantId): Collection
    {
        return $this->moduleRepository->findAllEnabledForTenant($tenantId);
    }

    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->moduleRepository->findByTenant($tenantId, $perPage);
    }

    public function create(ModuleRegistryDto $dto): ModuleRegistry
    {
        if ($this->moduleRepository->existsByKey($dto->tenantId, $dto->moduleKey)) {
            throw new ConfigurationException(
                "Module '{$dto->moduleKey}' is already registered for this tenant.",
                409,
            );
        }

        return $this->moduleRepository->create($dto->toArray());
    }

    public function update(string $id, ModuleRegistryDto $dto): ModuleRegistry
    {
        $this->findById($id);

        return $this->moduleRepository->update($id, $dto->toArray());
    }

    public function delete(string $id): void
    {
        $module = $this->findById($id);

        // Guard: prevent deletion if other active modules depend on this one
        $dependents = $this->moduleRepository->findDependents($module->tenant_id, $module->module_key);
        $enabledDependents = $dependents->where('is_enabled', true);

        if ($enabledDependents->isNotEmpty()) {
            $keys = $enabledDependents->pluck('module_key')->implode(', ');
            throw new ConfigurationException(
                "Cannot delete module '{$module->module_key}': it is required by [{$keys}].",
                409,
            );
        }

        $this->moduleRepository->delete($id);
    }

    public function toggle(string $id): ModuleRegistry
    {
        $module = $this->findById($id);

        // Guard: if disabling, check no enabled modules depend on this one
        if ($module->is_enabled) {
            $dependents = $this->moduleRepository->findDependents($module->tenant_id, $module->module_key);
            $enabledDependents = $dependents->where('is_enabled', true);

            if ($enabledDependents->isNotEmpty()) {
                $keys = $enabledDependents->pluck('module_key')->implode(', ');
                throw new ConfigurationException(
                    "Cannot disable module '{$module->module_key}': it is required by [{$keys}].",
                    409,
                );
            }
        }

        return $this->moduleRepository->toggle($id);
    }

    public function findById(string $id): ModuleRegistry
    {
        $module = $this->moduleRepository->findById($id);

        if ($module === null) {
            throw new ConfigurationException("Module not found with ID: {$id}", 404);
        }

        return $module;
    }
}
