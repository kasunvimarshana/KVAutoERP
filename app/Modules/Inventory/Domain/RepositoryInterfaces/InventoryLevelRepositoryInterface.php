<?php
namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\InventoryLevel;

interface InventoryLevelRepositoryInterface
{
    public function findById(int $id): ?InventoryLevel;
    public function findByProductWarehouseLocation(int $productId, int $warehouseId, int $locationId, ?int $batchId = null): ?InventoryLevel;
    public function findByProduct(int $productId, int $tenantId): array;
    public function findAll(int $tenantId, array $filters = [], int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
    public function create(array $data): InventoryLevel;
    public function update(InventoryLevel $level, array $data): InventoryLevel;
    public function save(InventoryLevel $level): InventoryLevel;
}
