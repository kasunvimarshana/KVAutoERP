<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\RepositoryInterfaces;
use Modules\Inventory\Domain\Entities\StockItem;
interface StockItemRepositoryInterface {
    public function findByProduct(int $tenantId, int $productId): array;
    public function findByWarehouse(int $tenantId, int $warehouseId): array;
    public function findByProductAndWarehouse(int $tenantId, int $productId, int $warehouseId): ?StockItem;
    public function save(StockItem $item): StockItem;
}
