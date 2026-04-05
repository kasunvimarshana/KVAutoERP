<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

class StockMovementService implements StockMovementServiceInterface
{
    public function __construct(
        private readonly StockMovementRepositoryInterface $movementRepository,
        private readonly StockItemRepositoryInterface $stockItemRepository,
        private readonly AddValuationLayerServiceInterface $valuationLayerService,
    ) {}

    public function receive(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        float $unitCost,
        ?string $referenceType,
        ?int $referenceId,
        ?array $batchData,
        ?int $performedBy,
    ): StockMovement {
        $now = new \DateTimeImmutable();

        $movement = $this->movementRepository->create([
            'tenant_id'      => $tenantId,
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'warehouse_id'   => $warehouseId,
            'location_id'    => $locationId,
            'type'           => 'receipt',
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'quantity'       => $quantity,
            'direction'      => 'in',
            'unit_cost'      => $unitCost,
            'batch_id'       => $batchData['batch_id'] ?? null,
            'lot_number'     => $batchData['lot_number'] ?? null,
            'serial_number'  => $batchData['serial_number'] ?? null,
            'performed_by'   => $performedBy,
            'performed_at'   => $now,
            'metadata'       => [],
        ]);

        $existing = $this->stockItemRepository->findByProduct(
            $tenantId, $productId, $variantId, $warehouseId, $locationId,
        );

        if ($existing !== null) {
            $newQty  = $existing->getQuantity() + $quantity;
            $newCost = $existing->getQuantity() > 0
                ? (($existing->getQuantity() * $existing->getUnitCost()) + ($quantity * $unitCost)) / $newQty
                : $unitCost;

            $this->stockItemRepository->updateQuantity($existing->getId(), $newQty);

            if ($existing->getUnitCost() !== $newCost) {
                $this->stockItemRepository->update($existing->getId(), [
                    'unit_cost'        => $newCost,
                    'last_movement_at' => $now,
                ]);
            } else {
                $this->stockItemRepository->update($existing->getId(), [
                    'last_movement_at' => $now,
                ]);
            }
        } else {
            $this->stockItemRepository->upsert(
                $tenantId, $productId, $variantId, $warehouseId, $locationId, $quantity, $unitCost,
            );
        }

        $this->valuationLayerService->add(
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $locationId,
            $quantity,
            $unitCost,
            'fifo',
            $batchData['batch_id'] ?? null,
        );

        return $movement;
    }

    public function issue(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        ?string $referenceType,
        ?int $referenceId,
        ?int $performedBy,
    ): StockMovement {
        $now = new \DateTimeImmutable();

        $movement = $this->movementRepository->create([
            'tenant_id'      => $tenantId,
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'warehouse_id'   => $warehouseId,
            'location_id'    => $locationId,
            'type'           => 'issue',
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'quantity'       => $quantity,
            'direction'      => 'out',
            'unit_cost'      => null,
            'performed_by'   => $performedBy,
            'performed_at'   => $now,
            'metadata'       => [],
        ]);

        $existing = $this->stockItemRepository->findByProduct(
            $tenantId, $productId, $variantId, $warehouseId, $locationId,
        );

        if ($existing !== null) {
            $newQty = max(0.0, $existing->getQuantity() - $quantity);
            $this->stockItemRepository->updateQuantity($existing->getId(), $newQty);
            $this->stockItemRepository->update($existing->getId(), ['last_movement_at' => $now]);
        }

        return $movement;
    }

    public function transfer(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $fromWarehouseId,
        ?int $fromLocationId,
        int $toWarehouseId,
        ?int $toLocationId,
        float $quantity,
        ?int $performedBy,
    ): array {
        $now = new \DateTimeImmutable();

        $outMovement = $this->movementRepository->create([
            'tenant_id'    => $tenantId,
            'product_id'   => $productId,
            'variant_id'   => $variantId,
            'warehouse_id' => $fromWarehouseId,
            'location_id'  => $fromLocationId,
            'type'         => 'transfer_out',
            'quantity'     => $quantity,
            'direction'    => 'out',
            'performed_by' => $performedBy,
            'performed_at' => $now,
            'metadata'     => [],
        ]);

        $fromItem = $this->stockItemRepository->findByProduct(
            $tenantId, $productId, $variantId, $fromWarehouseId, $fromLocationId,
        );

        $unitCost = 0.0;

        if ($fromItem !== null) {
            $unitCost = $fromItem->getUnitCost();
            $newQty   = max(0.0, $fromItem->getQuantity() - $quantity);
            $this->stockItemRepository->updateQuantity($fromItem->getId(), $newQty);
            $this->stockItemRepository->update($fromItem->getId(), ['last_movement_at' => $now]);
        }

        $inMovement = $this->movementRepository->create([
            'tenant_id'    => $tenantId,
            'product_id'   => $productId,
            'variant_id'   => $variantId,
            'warehouse_id' => $toWarehouseId,
            'location_id'  => $toLocationId,
            'type'         => 'transfer_in',
            'quantity'     => $quantity,
            'direction'    => 'in',
            'unit_cost'    => $unitCost,
            'performed_by' => $performedBy,
            'performed_at' => $now,
            'metadata'     => [],
        ]);

        $toItem = $this->stockItemRepository->findByProduct(
            $tenantId, $productId, $variantId, $toWarehouseId, $toLocationId,
        );

        if ($toItem !== null) {
            $newQty = $toItem->getQuantity() + $quantity;
            $this->stockItemRepository->updateQuantity($toItem->getId(), $newQty);
            $this->stockItemRepository->update($toItem->getId(), ['last_movement_at' => $now]);
        } else {
            $this->stockItemRepository->upsert(
                $tenantId, $productId, $variantId, $toWarehouseId, $toLocationId, $quantity, $unitCost,
            );
        }

        return [$outMovement, $inMovement];
    }

    public function adjust(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        ?int $locationId,
        float $newQuantity,
        string $reason,
        ?int $performedBy,
    ): StockMovement {
        $now = new \DateTimeImmutable();

        $existing = $this->stockItemRepository->findByProduct(
            $tenantId, $productId, $variantId, $warehouseId, $locationId,
        );

        $currentQty = $existing !== null ? $existing->getQuantity() : 0.0;
        $variance   = $newQuantity - $currentQty;
        $direction  = $variance >= 0 ? 'in' : 'out';

        $movement = $this->movementRepository->create([
            'tenant_id'    => $tenantId,
            'product_id'   => $productId,
            'variant_id'   => $variantId,
            'warehouse_id' => $warehouseId,
            'location_id'  => $locationId,
            'type'         => 'adjustment',
            'quantity'     => abs($variance),
            'direction'    => $direction,
            'notes'        => $reason,
            'performed_by' => $performedBy,
            'performed_at' => $now,
            'metadata'     => ['previous_quantity' => $currentQty, 'new_quantity' => $newQuantity],
        ]);

        if ($existing !== null) {
            $this->stockItemRepository->updateQuantity($existing->getId(), $newQuantity);
            $this->stockItemRepository->update($existing->getId(), ['last_movement_at' => $now]);
        } else {
            $this->stockItemRepository->upsert(
                $tenantId, $productId, $variantId, $warehouseId, $locationId, $newQuantity, 0.0,
            );
        }

        return $movement;
    }
}
