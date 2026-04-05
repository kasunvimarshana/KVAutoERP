<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AllocateStockServiceInterface;
use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;
use Modules\Inventory\Domain\Entities\StockMovement;

class InventoryManagerService implements InventoryManagerServiceInterface
{
    public function __construct(
        private readonly StockMovementServiceInterface $movementService,
        private readonly AddValuationLayerServiceInterface $valuationLayerService,
        private readonly AllocateStockServiceInterface $allocationService,
    ) {}

    public function receiveInventory(int $tenantId, array $data): StockMovement
    {
        return $this->movementService->receive(
            tenantId:      $tenantId,
            productId:     (int) $data['product_id'],
            variantId:     isset($data['variant_id']) ? (int) $data['variant_id'] : null,
            warehouseId:   (int) $data['warehouse_id'],
            locationId:    isset($data['location_id']) ? (int) $data['location_id'] : null,
            quantity:      (float) $data['quantity'],
            unitCost:      (float) ($data['unit_cost'] ?? 0),
            referenceType: $data['reference_type'] ?? null,
            referenceId:   isset($data['reference_id']) ? (int) $data['reference_id'] : null,
            batchData:     $data['batch'] ?? null,
            performedBy:   isset($data['performed_by']) ? (int) $data['performed_by'] : null,
        );
    }

    public function issueInventory(int $tenantId, array $data): StockMovement
    {
        return $this->movementService->issue(
            tenantId:      $tenantId,
            productId:     (int) $data['product_id'],
            variantId:     isset($data['variant_id']) ? (int) $data['variant_id'] : null,
            warehouseId:   (int) $data['warehouse_id'],
            locationId:    isset($data['location_id']) ? (int) $data['location_id'] : null,
            quantity:      (float) $data['quantity'],
            referenceType: $data['reference_type'] ?? null,
            referenceId:   isset($data['reference_id']) ? (int) $data['reference_id'] : null,
            performedBy:   isset($data['performed_by']) ? (int) $data['performed_by'] : null,
        );
    }

    public function allocateAndIssue(int $tenantId, array $data): array
    {
        $strategy = $data['strategy'] ?? 'FIFO';

        $allocations = $this->allocationService->allocate(
            tenantId:    $tenantId,
            productId:   (int) $data['product_id'],
            variantId:   isset($data['variant_id']) ? (int) $data['variant_id'] : null,
            warehouseId: (int) $data['warehouse_id'],
            quantity:    (float) $data['quantity'],
            strategy:    $strategy,
        );

        $movements = [];

        foreach ($allocations as $allocation) {
            $movements[] = $this->movementService->issue(
                tenantId:      $tenantId,
                productId:     (int) $data['product_id'],
                variantId:     isset($data['variant_id']) ? (int) $data['variant_id'] : null,
                warehouseId:   (int) $data['warehouse_id'],
                locationId:    $allocation['locationId'],
                quantity:      $allocation['quantity'],
                referenceType: $data['reference_type'] ?? null,
                referenceId:   isset($data['reference_id']) ? (int) $data['reference_id'] : null,
                performedBy:   isset($data['performed_by']) ? (int) $data['performed_by'] : null,
            );
        }

        return $movements;
    }
}
