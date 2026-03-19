<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\WorkflowDefinitionDto;
use App\Models\WorkflowDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WorkflowDefinitionServiceInterface
{
    /**
     * Retrieve the active workflow definition for a given entity type.
     */
    public function getWorkflowDefinition(string $tenantId, string $entityType): ?WorkflowDefinition;

    /**
     * Paginated list of all workflow definitions for a tenant.
     */
    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new workflow definition.
     */
    public function create(WorkflowDefinitionDto $dto): WorkflowDefinition;

    /**
     * Update an existing workflow definition (bumps version automatically).
     */
    public function update(string $id, WorkflowDefinitionDto $dto): WorkflowDefinition;

    /**
     * Delete a workflow definition (soft-delete).
     */
    public function delete(string $id): void;

    /**
     * Find a workflow definition by its ID.
     */
    public function findById(string $id): WorkflowDefinition;
}
