<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;

/**
 * WarehouseStock domain entity.
 *
 * Tracks how much of a specific product is held in a specific warehouse,
 * including any reserved (pending) quantities.
 */
final class WarehouseStock
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $warehouseId,
        public readonly string $productId,
        public int $quantity,
        public int $reservedQuantity,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    /**
     * Construct from a raw array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            warehouseId: $data['warehouse_id'],
            productId: $data['product_id'],
            quantity: (int) ($data['quantity'] ?? 0),
            reservedQuantity: (int) ($data['reserved_quantity'] ?? 0),
            updatedAt: isset($data['updated_at'])
                ? new DateTimeImmutable($data['updated_at'])
                : new DateTimeImmutable(),
        );
    }

    /**
     * The quantity available for new reservations (total minus reserved).
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reservedQuantity);
    }

    /**
     * Whether the given amount can be fulfilled from available stock.
     */
    public function canFulfill(int $amount): bool
    {
        return $this->getAvailableQuantity() >= $amount;
    }

    /**
     * Convert to plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'tenant_id'          => $this->tenantId,
            'warehouse_id'       => $this->warehouseId,
            'product_id'         => $this->productId,
            'quantity'           => $this->quantity,
            'reserved_quantity'  => $this->reservedQuantity,
            'available_quantity' => $this->getAvailableQuantity(),
            'updated_at'         => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
