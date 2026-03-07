<?php

namespace App\Modules\Inventory\DTOs;

class InventoryWebhookDTO
{
    public function __construct(
        public readonly string $event,
        public readonly string $inventoryId,
        public readonly string $tenantId,
        public readonly string $productId,
        public readonly int $quantity,
        public readonly string $status,
        public readonly array $context = [],
        public readonly ?string $timestamp = null,
    ) {}

    public static function fromPayload(array $payload): self
    {
        return new self(
            event:       $payload['event'] ?? '',
            inventoryId: $payload['inventory_id'] ?? '',
            tenantId:    $payload['tenant_id'] ?? '',
            productId:   $payload['product_id'] ?? '',
            quantity:    (int) ($payload['quantity'] ?? 0),
            status:      $payload['status'] ?? 'in_stock',
            context:     $payload['context'] ?? [],
            timestamp:   $payload['timestamp'] ?? null,
        );
    }
}
