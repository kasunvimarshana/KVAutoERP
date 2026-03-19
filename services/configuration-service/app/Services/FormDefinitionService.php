<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\FormDefinitionRepositoryInterface;
use App\Contracts\Services\FormDefinitionServiceInterface;
use App\DTOs\FormDefinitionDto;
use App\Exceptions\ConfigurationException;
use App\Models\FormDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FormDefinitionService implements FormDefinitionServiceInterface
{
    public function __construct(
        private readonly FormDefinitionRepositoryInterface $formRepository,
    ) {}

    public function getFormDefinition(string $tenantId, string $serviceName, string $entityType): ?FormDefinition
    {
        return $this->formRepository->findByEntity($tenantId, $serviceName, $entityType);
    }

    public function listForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->formRepository->findByTenant($tenantId, $perPage);
    }

    public function create(FormDefinitionDto $dto): FormDefinition
    {
        $nextVersion = $this->resolveNextVersion($dto->tenantId, $dto->serviceName, $dto->entityType);
        $data = array_merge($dto->toArray(), ['version' => $nextVersion]);

        return $this->formRepository->create($data);
    }

    public function update(string $id, FormDefinitionDto $dto): FormDefinition
    {
        $existing = $this->findById($id);
        $nextVersion = $existing->version + 1;
        $data = array_merge($dto->toArray(), ['version' => $nextVersion]);

        return $this->formRepository->update($id, $data);
    }

    public function delete(string $id): void
    {
        $this->findById($id);
        $this->formRepository->delete($id);
    }

    public function findById(string $id): FormDefinition
    {
        $form = $this->formRepository->findById($id);

        if ($form === null) {
            throw new ConfigurationException("Form definition not found with ID: {$id}", 404);
        }

        return $form;
    }

    private function resolveNextVersion(string $tenantId, string $serviceName, string $entityType): int
    {
        $latest = $this->formRepository->getLatestVersion($tenantId, $serviceName, $entityType);

        return $latest === null ? 1 : $latest->version + 1;
    }
}
