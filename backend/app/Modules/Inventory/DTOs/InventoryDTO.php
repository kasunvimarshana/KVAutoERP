<?php

namespace App\Modules\Inventory\DTOs;

class InventoryDTO
{
    public function __construct(
        public readonly ?int $productId = null,
        public readonly ?int $tenantId = null,
        public readonly ?int $quantity = null,
        public readonly ?int $reservedQuantity = null,
        public readonly ?int $minQuantity = null,
        public readonly ?int $maxQuantity = null,
        public readonly ?string $location = null,
        public readonly ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            quantity: $data['quantity'] ?? null,
            reservedQuantity: $data['reserved_quantity'] ?? null,
            minQuantity: $data['min_quantity'] ?? null,
            maxQuantity: $data['max_quantity'] ?? null,
            location: $data['location'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
