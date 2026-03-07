<?php

namespace App\Domain\Inventory\Events;

class InventoryCreated
{
    public function __construct(
        public readonly string $inventoryId,
        public readonly string $tenantId,
        public readonly array $data,
        public readonly \DateTimeImmutable $occurredAt
    ) {}

    public static function make(string $inventoryId, string $tenantId, array $data): self
    {
        return new self($inventoryId, $tenantId, $data, new \DateTimeImmutable());
    }

    public function toArray(): array
    {
        return [
            'event'        => 'inventory.created',
            'inventory_id' => $this->inventoryId,
            'tenant_id'    => $this->tenantId,
            'data'         => $this->data,
            'occurred_at'  => $this->occurredAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
