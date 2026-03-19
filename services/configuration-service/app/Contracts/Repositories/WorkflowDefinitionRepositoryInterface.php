<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\WorkflowDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WorkflowDefinitionRepositoryInterface
{
    public function findById(string $id): ?WorkflowDefinition;

    public function findByEntity(string $tenantId, string $entityType): ?WorkflowDefinition;

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    public function findAllByTenant(string $tenantId): Collection;

    public function create(array $data): WorkflowDefinition;

    public function update(string $id, array $data): WorkflowDefinition;

    public function delete(string $id): bool;

    public function existsByName(string $tenantId, string $name): bool;

    public function getActiveByEntity(string $tenantId, string $entityType): ?WorkflowDefinition;
}
