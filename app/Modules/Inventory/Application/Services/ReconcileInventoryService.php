<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountLineRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

class ReconcileInventoryService implements ReconcileInventoryServiceInterface
{
    public function __construct(
        private readonly CycleCountRepositoryInterface $cycleCountRepository,
        private readonly CycleCountLineRepositoryInterface $cycleCountLineRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
        private readonly StockItemRepositoryInterface $stockItemRepository,
    ) {}

    public function reconcile(int $cycleCountId): int
    {
        $cycleCount = $this->cycleCountRepository->findById($cycleCountId);

        if ($cycleCount === null) {
            throw new NotFoundException('CycleCount', $cycleCountId);
        }

        $lines    = $this->cycleCountLineRepository->findByCycleCount($cycleCountId);
        $now      = new \DateTimeImmutable();
        $applied  = 0;

        foreach ($lines as $line) {
            if (! $line->isVarianceSignificant()) {
                continue;
            }

            $variance  = $line->getVariance() ?? 0.0;
            $direction = $variance >= 0 ? 'in' : 'out';

            $this->movementRepository->create([
                'tenant_id'      => $cycleCount->getTenantId(),
                'product_id'     => $line->getProductId(),
                'variant_id'     => $line->getVariantId(),
                'warehouse_id'   => $cycleCount->getWarehouseId(),
                'location_id'    => $line->getLocationId(),
                'type'           => 'cycle_count',
                'reference_type' => 'cycle_count',
                'reference_id'   => $cycleCountId,
                'quantity'       => abs($variance),
                'direction'      => $direction,
                'notes'          => 'Cycle count reconciliation',
                'performed_at'   => $now,
                'metadata'       => [
                    'expected' => $line->getExpectedQuantity(),
                    'counted'  => $line->getCountedQuantity(),
                ],
            ]);

            // Update stock item to the counted quantity
            $stock = $this->stockItemRepository->findByProduct(
                $cycleCount->getTenantId(),
                $line->getProductId(),
                $line->getVariantId(),
                $cycleCount->getWarehouseId(),
                $line->getLocationId(),
            );

            $countedQty = $line->getCountedQuantity() ?? $line->getExpectedQuantity();

            if ($stock !== null) {
                $this->stockItemRepository->updateQuantity($stock->getId(), $countedQty);
                $this->stockItemRepository->update($stock->getId(), ['last_movement_at' => $now]);
            } else {
                $this->stockItemRepository->upsert(
                    $cycleCount->getTenantId(),
                    $line->getProductId(),
                    $line->getVariantId(),
                    $cycleCount->getWarehouseId(),
                    $line->getLocationId(),
                    $countedQty,
                    0.0,
                );
            }

            $this->cycleCountLineRepository->update($line->getId(), ['status' => 'counted']);

            $applied++;
        }

        $this->cycleCountRepository->update($cycleCountId, [
            'status'       => 'completed',
            'completed_at' => $now,
        ]);

        return $applied;
    }
}
