<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\WorkflowDefinitionRepositoryInterface;
use App\Models\WorkflowDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WorkflowDefinitionRepository implements WorkflowDefinitionRepositoryInterface
{
    public function findById(string $id): ?WorkflowDefinition
    {
        return WorkflowDefinition::find($id);
    }

    public function findByEntity(string $tenantId, string $entityType): ?WorkflowDefinition
    {
        return WorkflowDefinition::forTenant($tenantId)
            ->forEntity($entityType)
            ->active()
            ->orderByDesc('version')
            ->first();
    }

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return WorkflowDefinition::forTenant($tenantId)
            ->orderBy('entity_type')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findAllByTenant(string $tenantId): Collection
    {
        return WorkflowDefinition::forTenant($tenantId)->orderBy('entity_type')->get();
    }

    public function create(array $data): WorkflowDefinition
    {
        return WorkflowDefinition::create($data);
    }

    public function update(string $id, array $data): WorkflowDefinition
    {
        $workflow = WorkflowDefinition::findOrFail($id);
        $workflow->update($data);

        return $workflow->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) WorkflowDefinition::findOrFail($id)->delete();
    }

    public function existsByName(string $tenantId, string $name): bool
    {
        return WorkflowDefinition::forTenant($tenantId)
            ->where('name', $name)
            ->exists();
    }

    public function getActiveByEntity(string $tenantId, string $entityType): ?WorkflowDefinition
    {
        return WorkflowDefinition::forTenant($tenantId)
            ->forEntity($entityType)
            ->active()
            ->first();
    }
}
