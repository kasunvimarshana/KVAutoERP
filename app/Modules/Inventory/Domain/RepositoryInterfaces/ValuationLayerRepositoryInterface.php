<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\RepositoryInterfaces;

use Modules\Inventory\Domain\Entities\ValuationLayer;

interface ValuationLayerRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?ValuationLayer;

    public function findByProductAndLocation(
        int $productId,
        ?int $variantId,
        int $locationId,
        int $tenantId,
        string $method,
    ): array;

    public function create(array $data): ValuationLayer;

    public function update(int $id, array $data): ValuationLayer;

    /**
     * Returns layers ordered for consumption based on the valuation method:
     * - fifo: oldest received first (received_at ASC)
     * - lifo: newest received first (received_at DESC)
     * - fefo: earliest expiry first (joined with batch_lots)
     * Only layers with remaining_quantity > 0 are returned.
     */
    public function getLayersForConsumption(
        int $productId,
        ?int $variantId,
        int $locationId,
        int $tenantId,
        string $method,
    ): array;
}
