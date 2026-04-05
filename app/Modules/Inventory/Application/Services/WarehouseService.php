<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Inventory\Application\Contracts\WarehouseServiceInterface;
use Modules\Inventory\Domain\Entities\Warehouse;
use Modules\Inventory\Domain\RepositoryInterfaces\WarehouseRepositoryInterface;

final class WarehouseService implements WarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    public function getById(int $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if ($warehouse === null) {
            throw new NotFoundException('Warehouse', $id);
        }

        return $warehouse;
    }

    public function getByTenant(int $tenantId): Collection
    {
        return $this->warehouseRepository->findByTenant($tenantId);
    }

    public function create(array $data): Warehouse
    {
        return $this->warehouseRepository->create($data);
    }

    public function update(int $id, array $data): Warehouse
    {
        $warehouse = $this->warehouseRepository->update($id, $data);

        if ($warehouse === null) {
            throw new NotFoundException('Warehouse', $id);
        }

        return $warehouse;
    }

    public function delete(int $id): bool
    {
        return $this->warehouseRepository->delete($id);
    }

    public function setDefault(int $warehouseId, int $tenantId): Warehouse
    {
        return DB::transaction(function () use ($warehouseId, $tenantId): Warehouse {
            $warehouses = $this->warehouseRepository->findByTenant($tenantId);

            foreach ($warehouses as $warehouse) {
                if ($warehouse->isDefault) {
                    $this->warehouseRepository->update($warehouse->id, ['is_default' => false]);
                }
            }

            $updated = $this->warehouseRepository->update($warehouseId, ['is_default' => true]);

            if ($updated === null) {
                throw new NotFoundException('Warehouse', $warehouseId);
            }

            return $updated;
        });
    }
}
