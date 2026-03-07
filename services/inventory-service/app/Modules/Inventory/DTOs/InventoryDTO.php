<?php

namespace App\Modules\Inventory\DTOs;

class InventoryDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productSku,
        public readonly int $quantity,
        public readonly int $reservedQuantity = 0,
        public readonly ?string $warehouseLocation = null,
        public readonly int $reorderLevel = 10,
        public readonly int $reorderQuantity = 50,
        public readonly ?float $unitCost = null,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            productId:         (int) $data['product_id'],
            productSku:        $data['product_sku'],
            quantity:          (int) $data['quantity'],
            reservedQuantity:  (int) ($data['reserved_quantity'] ?? 0),
            warehouseLocation: $data['warehouse_location'] ?? null,
            reorderLevel:      (int) ($data['reorder_level'] ?? 10),
            reorderQuantity:   (int) ($data['reorder_quantity'] ?? 50),
            unitCost:          isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            notes:             $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'product_id'         => $this->productId,
            'product_sku'        => $this->productSku,
            'quantity'           => $this->quantity,
            'reserved_quantity'  => $this->reservedQuantity,
            'warehouse_location' => $this->warehouseLocation,
            'reorder_level'      => $this->reorderLevel,
            'reorder_quantity'   => $this->reorderQuantity,
            'unit_cost'          => $this->unitCost,
            'notes'              => $this->notes,
        ], fn ($v) => $v !== null);
    }
}
