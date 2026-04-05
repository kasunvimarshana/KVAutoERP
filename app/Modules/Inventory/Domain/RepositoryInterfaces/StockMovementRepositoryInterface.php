<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\RepositoryInterfaces;
use Modules\Inventory\Domain\Entities\StockMovement;
interface StockMovementRepositoryInterface {
    public function findByProduct(int $tenantId, int $productId): array;
    public function findByWarehouse(int $tenantId, int $warehouseId): array;
    public function save(StockMovement $movement): StockMovement;
}
