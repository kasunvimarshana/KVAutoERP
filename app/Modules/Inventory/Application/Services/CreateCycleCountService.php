<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountLineRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;

class CreateCycleCountService implements CreateCycleCountServiceInterface
{
    public function __construct(
        private readonly CycleCountRepositoryInterface $cycleCountRepository,
        private readonly CycleCountLineRepositoryInterface $cycleCountLineRepository,
        private readonly StockItemRepositoryInterface $stockItemRepository,
    ) {}

    public function createForWarehouse(int $tenantId, int $warehouseId, ?int $createdBy): CycleCount
    {
        $cycleCount = $this->cycleCountRepository->create([
            'tenant_id'    => $tenantId,
            'warehouse_id' => $warehouseId,
            'status'       => 'pending',
            'created_by'   => $createdBy,
            'started_at'   => new \DateTimeImmutable(),
        ]);

        $stockItems = $this->stockItemRepository->findByWarehouse($tenantId, $warehouseId);

        $lines = [];

        foreach ($stockItems as $item) {
            $lines[] = [
                'cycle_count_id'    => $cycleCount->getId(),
                'product_id'        => $item->getProductId(),
                'variant_id'        => $item->getVariantId(),
                'location_id'       => $item->getLocationId(),
                'batch_id'          => null,
                'expected_quantity' => $item->getQuantity(),
                'counted_quantity'  => null,
                'variance'          => null,
                'status'            => 'pending',
            ];
        }

        if (! empty($lines)) {
            $this->cycleCountLineRepository->bulkCreate($lines);
        }

        return $cycleCount;
    }
}
