<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\ValuationLayer;

interface ValuationLayerRepositoryInterface
{
    public function findByProduct(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
    ): array;

    public function create(array $data): ValuationLayer;

    public function update(int $id, array $data): ?ValuationLayer;

    /**
     * Returns available (quantity > 0) layers ordered for consumption:
     * FIFO → created_at ASC, LIFO → created_at DESC, AVERAGE → any order.
     */
    public function findAvailable(
        int $tenantId,
        int $productId,
        ?int $variantId,
        string $method,
    ): array;
}
