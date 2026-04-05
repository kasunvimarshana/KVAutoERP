<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Entities;

class OrderLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $orderId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly string $description,
        private readonly float $quantity,
        private readonly float $unitPrice,
        private readonly float $discountAmount,
        private readonly float $taxAmount,
        private readonly ?int $taxGroupId,
        private readonly float $totalAmount,
        private readonly ?int $warehouseId,
        private readonly ?int $locationId,
        private readonly ?int $batchId,
        private readonly ?string $notes,
        private readonly array $metadata,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getOrderId(): int { return $this->orderId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getTaxGroupId(): ?int { return $this->taxGroupId; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getWarehouseId(): ?int { return $this->warehouseId; }
    public function getLocationId(): ?int { return $this->locationId; }
    public function getBatchId(): ?int { return $this->batchId; }
    public function getNotes(): ?string { return $this->notes; }
    public function getMetadata(): array { return $this->metadata; }

    public function getLineTotal(): float
    {
        return $this->quantity * $this->unitPrice - $this->discountAmount + $this->taxAmount;
    }
}
