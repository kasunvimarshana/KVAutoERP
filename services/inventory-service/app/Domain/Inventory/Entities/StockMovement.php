<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\Enums\StockMovementType;
use DateTimeImmutable;

/**
 * StockMovement domain entity.
 *
 * An immutable record of every stock change event for a product.
 */
final class StockMovement
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $productId,
        public readonly StockMovementType $type,
        public readonly int $quantity,
        public readonly string $reference,
        public readonly string $reason,
        public readonly int $previousQuantity,
        public readonly int $newQuantity,
        public readonly string $performedBy,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    /**
     * Construct a StockMovement from a raw array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            productId: $data['product_id'],
            type: StockMovementType::from($data['type']),
            quantity: (int) $data['quantity'],
            reference: $data['reference'] ?? '',
            reason: $data['reason'] ?? '',
            previousQuantity: (int) ($data['previous_quantity'] ?? 0),
            newQuantity: (int) ($data['new_quantity'] ?? 0),
            performedBy: $data['performed_by'] ?? 'system',
            createdAt: isset($data['created_at'])
                ? new DateTimeImmutable($data['created_at'])
                : new DateTimeImmutable(),
        );
    }

    /**
     * Convert to plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'tenant_id'         => $this->tenantId,
            'product_id'        => $this->productId,
            'type'              => $this->type->value,
            'type_label'        => $this->type->label(),
            'quantity'          => $this->quantity,
            'reference'         => $this->reference,
            'reason'            => $this->reason,
            'previous_quantity' => $this->previousQuantity,
            'new_quantity'      => $this->newQuantity,
            'performed_by'      => $this->performedBy,
            'created_at'        => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * The net change in stock (positive = increase, negative = decrease).
     */
    public function netChange(): int
    {
        return $this->newQuantity - $this->previousQuantity;
    }
}
