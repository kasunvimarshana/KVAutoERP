<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Entities;

class ReturnLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $returnId,
        private readonly ?int $orderLineId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly ?int $batchId,
        private readonly float $quantity,
        private readonly string $condition, // good|damaged|defective
        private readonly float $unitPrice,
        private readonly ?int $restockToWarehouseId,
        private readonly ?int $restockToLocationId,
        private readonly bool $shouldRestock,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getReturnId(): int { return $this->returnId; }
    public function getOrderLineId(): ?int { return $this->orderLineId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getBatchId(): ?int { return $this->batchId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getCondition(): string { return $this->condition; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getRestockToWarehouseId(): ?int { return $this->restockToWarehouseId; }
    public function getRestockToLocationId(): ?int { return $this->restockToLocationId; }
    public function isShouldRestock(): bool { return $this->shouldRestock; }
    public function getNotes(): ?string { return $this->notes; }

    public function shouldBeRestocked(): bool
    {
        return $this->shouldRestock && $this->condition === 'good';
    }
}
