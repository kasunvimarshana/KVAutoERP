<?php declare(strict_types=1);
namespace Modules\Warehouse\Domain\RepositoryInterfaces;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
interface WarehouseLocationRepositoryInterface {
    public function findById(int $id): ?WarehouseLocation;
    public function findByWarehouse(int $warehouseId): array;
    public function findDescendants(int $id): array;
    public function save(WarehouseLocation $location): WarehouseLocation;
    public function delete(int $id): void;
}
