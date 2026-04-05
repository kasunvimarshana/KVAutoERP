<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\RepositoryInterfaces;
use Modules\Inventory\Domain\Entities\ValuationLayer;
interface ValuationLayerRepositoryInterface {
    /** @return ValuationLayer[] oldest first (FIFO order) */
    public function findActiveByProduct(int $tenantId, int $productId, int $warehouseId): array;
    /** @return ValuationLayer[] newest first (LIFO order) */
    public function findActiveByProductDesc(int $tenantId, int $productId, int $warehouseId): array;
    /** @return ValuationLayer[] expiry date first (FEFO order) */
    public function findActiveByExpiry(int $tenantId, int $productId, int $warehouseId): array;
    public function save(ValuationLayer $layer): ValuationLayer;
}
