<?php

namespace App\Domain\Inventory\Events;

class StockDepleted
{
    public function __construct(
        public readonly string $inventoryId,
        public readonly string $tenantId,
        public readonly string $sku,
        public readonly int $previousQuantity,
        public readonly \DateTimeImmutable $occurredAt
    ) {}

    public static function make(
        string $inventoryId,
        string $tenantId,
        string $sku,
        int $previousQuantity
    ): self {
        return new self($inventoryId, $tenantId, $sku, $previousQuantity, new \DateTimeImmutable());
    }

    public function toArray(): array
    {
        return [
            'event'             => 'inventory.stock.depleted',
            'inventory_id'      => $this->inventoryId,
            'tenant_id'         => $this->tenantId,
            'sku'               => $this->sku,
            'previous_quantity' => $this->previousQuantity,
            'occurred_at'       => $this->occurredAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
