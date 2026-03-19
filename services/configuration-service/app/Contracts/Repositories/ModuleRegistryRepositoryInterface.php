<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ModuleRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ModuleRegistryRepositoryInterface
{
    public function findById(string $id): ?ModuleRegistry;

    public function findByKey(string $tenantId, string $moduleKey): ?ModuleRegistry;

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function findAllEnabledForTenant(string $tenantId): Collection;

    public function create(array $data): ModuleRegistry;

    public function update(string $id, array $data): ModuleRegistry;

    public function delete(string $id): bool;

    public function toggle(string $id): ModuleRegistry;

    public function existsByKey(string $tenantId, string $moduleKey): bool;

    public function findDependents(string $tenantId, string $moduleKey): Collection;
}
