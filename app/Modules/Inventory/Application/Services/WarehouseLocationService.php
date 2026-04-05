<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Inventory\Application\Contracts\WarehouseLocationServiceInterface;
use Modules\Inventory\Domain\Entities\WarehouseLocation;
use Modules\Inventory\Domain\RepositoryInterfaces\WarehouseLocationRepositoryInterface;

final class WarehouseLocationService implements WarehouseLocationServiceInterface
{
    public function __construct(
        private readonly WarehouseLocationRepositoryInterface $locationRepository,
    ) {}

    public function getById(int $id): WarehouseLocation
    {
        $location = $this->locationRepository->findById($id);

        if ($location === null) {
            throw new NotFoundException('WarehouseLocation', $id);
        }

        return $location;
    }

    public function getTree(int $warehouseId): Collection
    {
        return $this->locationRepository->getTree($warehouseId);
    }

    public function getByWarehouse(int $warehouseId): Collection
    {
        return $this->locationRepository->findByWarehouse($warehouseId);
    }

    public function create(array $data): WarehouseLocation
    {
        if (isset($data['parent_id']) && $data['parent_id'] !== null) {
            $parent = $this->locationRepository->findById((int) $data['parent_id']);

            if ($parent === null) {
                throw new NotFoundException('WarehouseLocation', $data['parent_id']);
            }

            $data['path'] = $parent->path . $parent->id . '/';
            $data['level'] = $parent->level + 1;
        } else {
            $data['path'] = '/';
            $data['level'] = 0;
        }

        return $this->locationRepository->create($data);
    }

    public function update(int $id, array $data): WarehouseLocation
    {
        $location = $this->locationRepository->update($id, $data);

        if ($location === null) {
            throw new NotFoundException('WarehouseLocation', $id);
        }

        return $location;
    }

    public function delete(int $id): bool
    {
        return $this->locationRepository->delete($id);
    }
}
