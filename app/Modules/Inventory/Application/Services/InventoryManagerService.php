<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\ConsumeValuationLayersServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\RepositoryInterfaces\StockItemRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

final class InventoryManagerService implements InventoryManagerServiceInterface
{
    public function __construct(
        private readonly StockItemRepositoryInterface $stockItemRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
        private readonly AddValuationLayerServiceInterface $addValuationLayerService,
        private readonly ConsumeValuationLayersServiceInterface $consumeValuationLayersService,
    ) {}

    public function receive(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $locationId,
        float $qty,
        float $costPerUnit,
        ?string $batchNumber,
        ?string $lotNumber,
        ?string $serialNumber,
        ?string $expiryDate,
    ): void {
        $existing = $this->stockItemRepository->findPosition(
            $productId,
            $variantId,
            $warehouseId,
            $locationId,
        );

        if ($existing !== null) {
            $this->stockItemRepository->updateQuantity($existing->id, [
                'quantity_available' => $existing->quantityAvailable + $qty,
            ]);
        } else {
            $this->stockItemRepository->upsertPosition([
                'tenant_id'          => $tenantId,
                'product_id'         => $productId,
                'product_variant_id' => $variantId,
                'warehouse_id'       => $warehouseId,
                'location_id'        => $locationId,
                'quantity_available' => $qty,
                'unit_of_measure'    => 'unit',
            ]);
        }

        $this->addValuationLayerService->addLayer(
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $qty,
            $costPerUnit,
            'fifo',
            $batchNumber,
            $lotNumber,
            $serialNumber,
            $expiryDate,
        );

        $this->movementRepository->record([
            'tenant_id'          => $tenantId,
            'product_id'         => $productId,
            'product_variant_id' => $variantId,
            'to_location_id'     => $locationId,
            'quantity'           => $qty,
            'type'               => StockMovement::TYPE_RECEIPT,
            'batch_number'       => $batchNumber,
            'lot_number'         => $lotNumber,
            'serial_number'      => $serialNumber,
            'expiry_date'        => $expiryDate,
            'cost_per_unit'      => $costPerUnit,
            'moved_at'           => now()->toDateTimeString(),
        ]);
    }

    public function issue(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $locationId,
        float $qty,
        string $referenceType,
        int $referenceId,
    ): void {
        $costPerUnit = $this->consumeValuationLayersService->consume(
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $qty,
            'fifo',
        );

        $existing = $this->stockItemRepository->findPosition(
            $productId,
            $variantId,
            $warehouseId,
            $locationId,
        );

        if ($existing !== null) {
            $newAvailable = max(0.0, $existing->quantityAvailable - $qty);
            $this->stockItemRepository->updateQuantity($existing->id, [
                'quantity_available' => $newAvailable,
            ]);
        }

        $this->movementRepository->record([
            'tenant_id'          => $tenantId,
            'product_id'         => $productId,
            'product_variant_id' => $variantId,
            'from_location_id'   => $locationId,
            'quantity'           => $qty,
            'type'               => StockMovement::TYPE_ISSUE,
            'reference_type'     => $referenceType,
            'reference_id'       => $referenceId,
            'cost_per_unit'      => $costPerUnit,
            'moved_at'           => now()->toDateTimeString(),
        ]);
    }

    public function transfer(
        int $tenantId,
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $fromLocationId,
        int $toLocationId,
        float $qty,
    ): void {
        DB::transaction(function () use (
            $tenantId,
            $productId,
            $variantId,
            $warehouseId,
            $fromLocationId,
            $toLocationId,
            $qty,
        ): void {
            $fromItem = $this->stockItemRepository->findPosition(
                $productId,
                $variantId,
                $warehouseId,
                $fromLocationId,
            );

            if ($fromItem !== null) {
                $newFromAvailable = max(0.0, $fromItem->quantityAvailable - $qty);
                $this->stockItemRepository->updateQuantity($fromItem->id, [
                    'quantity_available' => $newFromAvailable,
                ]);
            }

            $toItem = $this->stockItemRepository->findPosition(
                $productId,
                $variantId,
                $warehouseId,
                $toLocationId,
            );

            if ($toItem !== null) {
                $this->stockItemRepository->updateQuantity($toItem->id, [
                    'quantity_available' => $toItem->quantityAvailable + $qty,
                ]);
            } else {
                $this->stockItemRepository->upsertPosition([
                    'tenant_id'          => $tenantId,
                    'product_id'         => $productId,
                    'product_variant_id' => $variantId,
                    'warehouse_id'       => $warehouseId,
                    'location_id'        => $toLocationId,
                    'quantity_available' => $qty,
                    'unit_of_measure'    => 'unit',
                ]);
            }

            $this->movementRepository->record([
                'tenant_id'          => $tenantId,
                'product_id'         => $productId,
                'product_variant_id' => $variantId,
                'from_location_id'   => $fromLocationId,
                'to_location_id'     => $toLocationId,
                'quantity'           => $qty,
                'type'               => StockMovement::TYPE_TRANSFER,
                'cost_per_unit'      => 0,
                'moved_at'           => now()->toDateTimeString(),
            ]);
        });
    }
}
