<?php

namespace App\Modules\Inventory\DTOs;

class InventoryDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $productId,
        public readonly int $quantity = 0,
        public readonly int $reservedQuantity = 0,
        public readonly int $minimumQuantity = 0,
        public readonly ?int $maximumQuantity = null,
        public readonly ?string $warehouseLocation = null,
        public readonly string $status = 'in_stock',
        public readonly ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId:           $data['tenant_id'],
            productId:          $data['product_id'],
            quantity:           (int) ($data['quantity'] ?? 0),
            reservedQuantity:   (int) ($data['reserved_quantity'] ?? 0),
            minimumQuantity:    (int) ($data['minimum_quantity'] ?? 0),
            maximumQuantity:    isset($data['maximum_quantity']) ? (int) $data['maximum_quantity'] : null,
            warehouseLocation:  $data['warehouse_location'] ?? null,
            status:             $data['status'] ?? 'in_stock',
            metadata:           $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'          => $this->tenantId,
            'product_id'         => $this->productId,
            'quantity'           => $this->quantity,
            'reserved_quantity'  => $this->reservedQuantity,
            'minimum_quantity'   => $this->minimumQuantity,
            'maximum_quantity'   => $this->maximumQuantity,
            'warehouse_location' => $this->warehouseLocation,
            'status'             => $this->status,
            'metadata'           => $this->metadata,
        ];
    }
}
