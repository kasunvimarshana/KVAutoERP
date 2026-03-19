<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\WorkflowDefinitionRepositoryInterface;
use App\Contracts\Services\WorkflowDefinitionServiceInterface;
use App\DTOs\WorkflowDefinitionDto;
use App\Exceptions\ConfigurationException;
use App\Models\WorkflowDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkflowDefinitionService implements WorkflowDefinitionServiceInterface
{
    public function __construct(
        private readonly WorkflowDefinitionRepositoryInterface $workflowRepository,
    ) {}

    public function getWorkflowDefinition(string $tenantId, string $entityType): ?WorkflowDefinition
    {
        return $this->workflowRepository->getActiveByEntity($tenantId, $entityType);
    }

    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->workflowRepository->findByTenant($tenantId, $perPage);
    }

    public function create(WorkflowDefinitionDto $dto): WorkflowDefinition
    {
        if ($this->workflowRepository->existsByName($dto->tenantId, $dto->name)) {
            throw new ConfigurationException(
                "Workflow definition '{$dto->name}' already exists for this tenant.",
                409,
            );
        }

        $latest = $this->workflowRepository->findByEntity($dto->tenantId, $dto->entityType);
        $version = $latest === null ? 1 : $latest->version + 1;
        $data = array_merge($dto->toArray(), ['version' => $version]);

        return $this->workflowRepository->create($data);
    }

    public function update(string $id, WorkflowDefinitionDto $dto): WorkflowDefinition
    {
        $existing = $this->findById($id);
        $data = array_merge($dto->toArray(), ['version' => $existing->version + 1]);

        return $this->workflowRepository->update($id, $data);
    }

    public function delete(string $id): void
    {
        $this->findById($id);
        $this->workflowRepository->delete($id);
    }

    public function findById(string $id): WorkflowDefinition
    {
        $workflow = $this->workflowRepository->findById($id);

        if ($workflow === null) {
            throw new ConfigurationException("Workflow definition not found with ID: {$id}", 404);
        }

        return $workflow;
    }
}
