<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Warehouse\Application\Contracts\WarehouseServiceInterface;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;

class WarehouseService implements WarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function create(array $data): Warehouse
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Warehouse
    {
        $warehouse = $this->repository->update($id, $data);

        if ($warehouse === null) {
            throw new NotFoundException('Warehouse', $id);
        }

        return $warehouse;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Warehouse
    {
        $warehouse = $this->repository->findById($id);

        if ($warehouse === null) {
            throw new NotFoundException('Warehouse', $id);
        }

        return $warehouse;
    }

    public function setDefault(int $tenantId, int $id): Warehouse
    {
        // Clear existing default for this tenant
        $current = $this->repository->findDefault($tenantId);

        if ($current !== null && $current->getId() !== $id) {
            $this->repository->update($current->getId(), ['is_default' => false]);
        }

        $warehouse = $this->repository->update($id, ['is_default' => true]);

        if ($warehouse === null) {
            throw new NotFoundException('Warehouse', $id);
        }

        return $warehouse;
    }

    public function all(int $tenantId): array
    {
        return $this->repository->all($tenantId);
    }

    public function findActive(int $tenantId): array
    {
        return $this->repository->findActive($tenantId);
    }
}
