<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ModuleRegistryRepositoryInterface;
use App\Models\ModuleRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ModuleRegistryRepository implements ModuleRegistryRepositoryInterface
{
    public function findById(string $id): ?ModuleRegistry
    {
        return ModuleRegistry::find($id);
    }

    public function findByKey(string $tenantId, string $moduleKey): ?ModuleRegistry
    {
        return ModuleRegistry::forTenant($tenantId)
            ->where('module_key', $moduleKey)
            ->first();
    }

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return ModuleRegistry::forTenant($tenantId)
            ->orderBy('module_key')
            ->paginate($perPage);
    }

    public function findAllEnabledForTenant(string $tenantId): Collection
    {
        return ModuleRegistry::forTenant($tenantId)
            ->enabled()
            ->orderBy('module_key')
            ->get();
    }

    public function create(array $data): ModuleRegistry
    {
        return ModuleRegistry::create($data);
    }

    public function update(string $id, array $data): ModuleRegistry
    {
        $module = ModuleRegistry::findOrFail($id);
        $module->update($data);

        return $module->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) ModuleRegistry::findOrFail($id)->delete();
    }

    public function toggle(string $id): ModuleRegistry
    {
        $module = ModuleRegistry::findOrFail($id);
        $module->update(['is_enabled' => ! $module->is_enabled]);

        return $module->fresh();
    }

    public function existsByKey(string $tenantId, string $moduleKey): bool
    {
        return ModuleRegistry::forTenant($tenantId)
            ->where('module_key', $moduleKey)
            ->exists();
    }

    public function findDependents(string $tenantId, string $moduleKey): Collection
    {
        return ModuleRegistry::forTenant($tenantId)
            ->whereJsonContains('dependencies', $moduleKey)
            ->get();
    }
}
