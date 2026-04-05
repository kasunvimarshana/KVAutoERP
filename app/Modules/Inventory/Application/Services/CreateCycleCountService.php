<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;

final class CreateCycleCountService implements CreateCycleCountServiceInterface
{
    public function __construct(
        private readonly CycleCountRepositoryInterface $cycleCountRepository,
        private readonly StockItemRepositoryInterface $stockItemRepository,
    ) {}

    public function create(
        int $tenantId,
        int $warehouseId,
        ?int $locationId,
        array $productIds,
        ?int $createdBy = null,
    ): CycleCount {
        $referenceNo = 'CC-' . date('YmdHis') . '-' . $tenantId;

        $cycleCount = $this->cycleCountRepository->create([
            'tenant_id'    => $tenantId,
            'warehouse_id' => $warehouseId,
            'location_id'  => $locationId,
            'reference_no' => $referenceNo,
            'status'       => CycleCount::STATUS_PENDING,
            'created_by'   => $createdBy,
        ]);

        foreach ($productIds as $productId) {
            $stockItems = $this->stockItemRepository->findByProduct($tenantId, (int) $productId);

            $stockItems = $stockItems->filter(
                fn ($item) => $item->warehouseId === $warehouseId
                    && ($locationId === null || $item->locationId === $locationId)
            );

            $systemQty = (float) $stockItems->sum(fn ($item) => $item->quantityAvailable);

            $this->cycleCountRepository->addLine($cycleCount->id, [
                'cycle_count_id'     => $cycleCount->id,
                'product_id'         => (int) $productId,
                'product_variant_id' => null,
                'system_qty'         => $systemQty,
                'counted_qty'        => null,
                'variance'           => null,
            ]);
        }

        return $cycleCount;
    }
}
