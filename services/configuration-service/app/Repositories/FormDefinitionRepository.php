<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\FormDefinitionRepositoryInterface;
use App\Models\FormDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FormDefinitionRepository implements FormDefinitionRepositoryInterface
{
    public function findById(string $id): ?FormDefinition
    {
        return FormDefinition::find($id);
    }

    public function findByEntity(string $tenantId, string $serviceName, string $entityType): ?FormDefinition
    {
        return FormDefinition::forTenant($tenantId)
            ->forService($serviceName)
            ->forEntity($entityType)
            ->active()
            ->orderByDesc('version')
            ->first();
    }

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return FormDefinition::forTenant($tenantId)
            ->orderBy('service_name')
            ->orderBy('entity_type')
            ->paginate($perPage);
    }

    public function findByService(string $tenantId, string $serviceName): Collection
    {
        return FormDefinition::forTenant($tenantId)
            ->forService($serviceName)
            ->orderBy('entity_type')
            ->get();
    }

    public function create(array $data): FormDefinition
    {
        return FormDefinition::create($data);
    }

    public function update(string $id, array $data): FormDefinition
    {
        $form = FormDefinition::findOrFail($id);
        $form->update($data);

        return $form->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) FormDefinition::findOrFail($id)->delete();
    }

    public function existsByEntity(string $tenantId, string $serviceName, string $entityType): bool
    {
        return FormDefinition::forTenant($tenantId)
            ->forService($serviceName)
            ->forEntity($entityType)
            ->exists();
    }

    public function getLatestVersion(string $tenantId, string $serviceName, string $entityType): ?FormDefinition
    {
        return FormDefinition::forTenant($tenantId)
            ->forService($serviceName)
            ->forEntity($entityType)
            ->orderByDesc('version')
            ->first();
    }
}
