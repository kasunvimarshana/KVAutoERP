<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\FormDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FormDefinitionRepositoryInterface
{
    public function findById(string $id): ?FormDefinition;

    public function findByEntity(string $tenantId, string $serviceName, string $entityType): ?FormDefinition;

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function findByService(string $tenantId, string $serviceName): Collection;

    public function create(array $data): FormDefinition;

    public function update(string $id, array $data): FormDefinition;

    public function delete(string $id): bool;

    public function existsByEntity(string $tenantId, string $serviceName, string $entityType): bool;

    public function getLatestVersion(string $tenantId, string $serviceName, string $entityType): ?FormDefinition;
}
