<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Domain\Entities\InventoryAdjustment;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryAdjustmentRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockRepositoryInterface;

class ReconcileInventoryService implements ReconcileInventoryServiceInterface
{
    public function __construct(
        private readonly InventoryAdjustmentRepositoryInterface $adjustmentRepo,
        private readonly StockMovementRepositoryInterface $movementRepo,
        private readonly StockRepositoryInterface $stockRepo,
    ) {}

    public function reconcile(int $tenantId, int $adjustmentId): InventoryAdjustment
    {
        $adjustment = $this->adjustmentRepo->findById($adjustmentId, $tenantId);
        if ($adjustment === null) {
            throw new \RuntimeException('Adjustment not found.');
        }

        if ($adjustment->status !== 'approved') {
            throw new \RuntimeException('Adjustment must be approved before applying.');
        }

        $lines = $this->adjustmentRepo->findLines($adjustmentId, $tenantId);

        foreach ($lines as $line) {
            $variance = $line->getVariance();
            if ($variance == 0.0) {
                continue;
            }

            $this->movementRepo->create([
                'tenant_id'        => $tenantId,
                'product_id'       => $line->productId,
                'variant_id'       => $line->variantId,
                'from_location_id' => $variance < 0.0 ? $adjustment->locationId : null,
                'to_location_id'   => $variance > 0.0 ? $adjustment->locationId : null,
                'quantity'         => abs($variance),
                'type'             => 'adjustment',
                'reference'        => $adjustment->adjustmentNumber,
                'cost'             => $line->unitCost,
            ]);

            $stockId = $this->resolveStockId(
                $line->productId,
                $line->variantId,
                $adjustment->locationId,
                $tenantId,
            );

            $this->stockRepo->updateQuantity($stockId, $variance, $tenantId);
        }

        return $this->adjustmentRepo->update($adjustmentId, ['status' => 'applied']);
    }

    private function resolveStockId(
        int $productId,
        ?int $variantId,
        int $locationId,
        int $tenantId,
    ): int {
        $stock = $this->stockRepo->findByProductAndLocation($productId, $variantId, $locationId, $tenantId);

        if ($stock === null) {
            $stock = $this->stockRepo->upsert([
                'tenant_id'         => $tenantId,
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'location_id'       => $locationId,
                'quantity'          => 0,
                'reserved_quantity' => 0,
                'unit'              => 'unit',
            ]);
        }

        return $stock->id;
    }
}
