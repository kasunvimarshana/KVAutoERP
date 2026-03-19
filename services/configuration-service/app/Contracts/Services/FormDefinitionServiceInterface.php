<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\FormDefinitionDto;
use App\Models\FormDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FormDefinitionServiceInterface
{
    /**
     * Retrieve the active form definition for a given service and entity type.
     */
    public function getFormDefinition(string $tenantId, string $serviceName, string $entityType): ?FormDefinition;

    /**
     * Paginated list of all form definitions for a tenant.
     */
    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new form definition.
     */
    public function create(FormDefinitionDto $dto): FormDefinition;

    /**
     * Update an existing form definition (bumps version automatically).
     */
    public function update(string $id, FormDefinitionDto $dto): FormDefinition;

    /**
     * Delete a form definition (soft-delete).
     */
    public function delete(string $id): void;

    /**
     * Find a form definition by its ID.
     */
    public function findById(string $id): FormDefinition;
}
