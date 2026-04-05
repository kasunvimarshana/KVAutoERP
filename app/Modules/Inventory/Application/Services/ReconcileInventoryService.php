<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Domain\Entities\StockAdjustment;
use Modules\Inventory\Domain\Entities\StockAdjustmentLine;
use Modules\Inventory\Domain\RepositoryInterfaces\StockAdjustmentRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

final class ReconcileInventoryService implements ReconcileInventoryServiceInterface
{
    public function __construct(
        private readonly StockAdjustmentRepositoryInterface $adjustmentRepository,
        private readonly StockItemRepositoryInterface $stockItemRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {}

    public function reconcile(int $adjustmentId, int $postedBy): StockAdjustment
    {
        $adjustment = $this->adjustmentRepository->findById($adjustmentId);

        if ($adjustment === null || !$adjustment->isDraft()) {
            throw new \RuntimeException('Adjustment not found or already posted / cancelled.');
        }

        return DB::transaction(function () use ($adjustment, $postedBy): StockAdjustment {
            $lines = $this->adjustmentRepository->getLines($adjustment->id);

            /** @var StockAdjustmentLine $line */
            foreach ($lines as $line) {
                $stockItem = $this->stockItemRepository->findPosition(
                    $line->productId,
                    $line->productVariantId,
                    $adjustment->warehouseId,
                    $adjustment->locationId,
                );

                $delta = $line->actualQty - $line->expectedQty;

                if ($stockItem !== null) {
                    $newAvailable = max(0.0, $stockItem->quantityAvailable + $delta);
                    $this->stockItemRepository->updateQuantity($stockItem->id, [
                        'quantity_available' => $newAvailable,
                    ]);
                } else {
                    $this->stockItemRepository->upsertPosition([
                        'tenant_id'          => $adjustment->tenantId,
                        'product_id'         => $line->productId,
                        'product_variant_id' => $line->productVariantId,
                        'warehouse_id'       => $adjustment->warehouseId,
                        'location_id'        => $adjustment->locationId,
                        'quantity_available' => max(0.0, $delta),
                        'unit_of_measure'    => 'unit',
                    ]);
                }

                $this->movementRepository->record([
                    'tenant_id'          => $adjustment->tenantId,
                    'product_id'         => $line->productId,
                    'product_variant_id' => $line->productVariantId,
                    'to_location_id'     => $adjustment->locationId,
                    'quantity'           => $delta,
                    'type'               => 'adjustment',
                    'reference_type'     => 'stock_adjustment',
                    'reference_id'       => $adjustment->id,
                    'batch_number'       => $line->batchNumber,
                    'lot_number'         => $line->lotNumber,
                    'serial_number'      => $line->serialNumber,
                    'cost_per_unit'      => $line->costPerUnit,
                    'moved_at'           => now()->toDateTimeString(),
                ]);
            }

            return $this->adjustmentRepository->update($adjustment->id, [
                'status'    => StockAdjustment::STATUS_POSTED,
                'posted_by' => $postedBy,
                'posted_at' => now()->toDateTimeString(),
            ]);
        });
    }
}
