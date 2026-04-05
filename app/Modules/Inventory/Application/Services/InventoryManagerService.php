<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\AddValuationLayerServiceInterface;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;
use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Domain\Entities\StockMovement;

class InventoryManagerService implements InventoryManagerServiceInterface
{
    public function __construct(
        private readonly StockMovementServiceInterface $movementService,
        private readonly AddValuationLayerServiceInterface $valuationService,
        private readonly StockServiceInterface $stockService,
    ) {}

    public function receive(array $data): StockMovement
    {
        $movement = $this->movementService->record([
            'tenant_id'        => $data['tenant_id'],
            'product_id'       => $data['product_id'],
            'variant_id'       => $data['variant_id'] ?? null,
            'from_location_id' => null,
            'to_location_id'   => $data['to_location_id'],
            'quantity'         => $data['quantity'],
            'type'             => 'receipt',
            'reference'        => $data['reference'] ?? null,
            'batch_number'     => $data['batch_number'] ?? null,
            'lot_number'       => $data['lot_number'] ?? null,
            'serial_number'    => $data['serial_number'] ?? null,
            'expiry_date'      => $data['expiry_date'] ?? null,
            'cost'             => $data['unit_cost'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'created_by'       => $data['created_by'] ?? null,
        ]);

        $this->valuationService->add(
            tenantId:   $data['tenant_id'],
            productId:  $data['product_id'],
            variantId:  $data['variant_id'] ?? null,
            locationId: $data['to_location_id'],
            quantity:   $data['quantity'],
            unitCost:   (float) ($data['unit_cost'] ?? 0.0),
            method:     $data['valuation_method'] ?? 'fifo',
            receivedAt: new \DateTimeImmutable(),
            reference:  $data['reference'] ?? null,
        );

        $this->stockService->updateStock(
            productId:  $data['product_id'],
            locationId: $data['to_location_id'],
            delta:      $data['quantity'],
            tenantId:   $data['tenant_id'],
            unit:       $data['unit'] ?? 'unit',
            variantId:  $data['variant_id'] ?? null,
        );

        return $movement;
    }

    public function issue(array $data): StockMovement
    {
        $movement = $this->movementService->record([
            'tenant_id'        => $data['tenant_id'],
            'product_id'       => $data['product_id'],
            'variant_id'       => $data['variant_id'] ?? null,
            'from_location_id' => $data['from_location_id'],
            'to_location_id'   => null,
            'quantity'         => $data['quantity'],
            'type'             => 'issue',
            'reference'        => $data['reference'] ?? null,
            'cost'             => $data['unit_cost'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'created_by'       => $data['created_by'] ?? null,
        ]);

        $this->stockService->updateStock(
            productId:  $data['product_id'],
            locationId: $data['from_location_id'],
            delta:      -$data['quantity'],
            tenantId:   $data['tenant_id'],
            unit:       $data['unit'] ?? 'unit',
            variantId:  $data['variant_id'] ?? null,
        );

        return $movement;
    }

    public function transfer(array $data): StockMovement
    {
        $movement = $this->movementService->record([
            'tenant_id'        => $data['tenant_id'],
            'product_id'       => $data['product_id'],
            'variant_id'       => $data['variant_id'] ?? null,
            'from_location_id' => $data['from_location_id'],
            'to_location_id'   => $data['to_location_id'],
            'quantity'         => $data['quantity'],
            'type'             => 'transfer',
            'reference'        => $data['reference'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'created_by'       => $data['created_by'] ?? null,
        ]);

        $this->stockService->updateStock(
            productId:  $data['product_id'],
            locationId: $data['from_location_id'],
            delta:      -$data['quantity'],
            tenantId:   $data['tenant_id'],
            variantId:  $data['variant_id'] ?? null,
        );

        $this->stockService->updateStock(
            productId:  $data['product_id'],
            locationId: $data['to_location_id'],
            delta:      $data['quantity'],
            tenantId:   $data['tenant_id'],
            variantId:  $data['variant_id'] ?? null,
        );

        return $movement;
    }
}
