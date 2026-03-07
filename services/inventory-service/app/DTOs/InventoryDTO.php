<?php
namespace App\DTOs;

use App\Models\Inventory;

class InventoryDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $tenantId,
        public readonly string  $productId,
        public readonly ?string $warehouseLocation,
        public readonly int     $quantity,
        public readonly int     $reservedQuantity,
        public readonly int     $availableQuantity,
        public readonly ?string $unit,
        public readonly int     $minLevel,
        public readonly int     $maxLevel,
        public readonly string  $status,
        public readonly ?string $notes,
        public readonly ?array  $product,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromModel(Inventory $inventory, ?array $product = null): self
    {
        return new self(
            id:                $inventory->id,
            tenantId:          $inventory->tenant_id,
            productId:         $inventory->product_id,
            warehouseLocation: $inventory->warehouse_location,
            quantity:          $inventory->quantity,
            reservedQuantity:  $inventory->reserved_quantity,
            availableQuantity: $inventory->available_quantity,
            unit:              $inventory->unit,
            minLevel:          $inventory->min_level,
            maxLevel:          $inventory->max_level,
            status:            $inventory->status,
            notes:             $inventory->notes,
            product:           $product,
            createdAt:         $inventory->created_at?->toIso8601String(),
            updatedAt:         $inventory->updated_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'tenant_id'          => $this->tenantId,
            'product_id'         => $this->productId,
            'warehouse_location' => $this->warehouseLocation,
            'quantity'           => $this->quantity,
            'reserved_quantity'  => $this->reservedQuantity,
            'available_quantity' => $this->availableQuantity,
            'unit'               => $this->unit,
            'min_level'          => $this->minLevel,
            'max_level'          => $this->maxLevel,
            'status'             => $this->status,
            'notes'              => $this->notes,
            'product'            => $this->product,
            'created_at'         => $this->createdAt,
            'updated_at'         => $this->updatedAt,
        ];
    }
}
