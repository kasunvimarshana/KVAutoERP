<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\Entities;

class PurchaseOrderLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $purchaseOrderId,
        private readonly int $productId,
        private readonly ?int $productVariantId,
        private readonly string $description,
        private float $quantity,
        private float $unitPrice,
        private float $discountRate,
        private float $taxRate,
        private float $totalPrice,
        private float $receivedQty,
        private readonly string $unitOfMeasure,
        private readonly ?string $notes,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getPurchaseOrderId(): int { return $this->purchaseOrderId; }
    public function getProductId(): int { return $this->productId; }
    public function getProductVariantId(): ?int { return $this->productVariantId; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getDiscountRate(): float { return $this->discountRate; }
    public function getTaxRate(): float { return $this->taxRate; }
    public function getTotalPrice(): float { return $this->totalPrice; }
    public function getReceivedQty(): float { return $this->receivedQty; }
    public function getUnitOfMeasure(): string { return $this->unitOfMeasure; }
    public function getNotes(): ?string { return $this->notes; }

    public function setQuantity(float $v): void { $this->quantity = $v; }
    public function setUnitPrice(float $v): void { $this->unitPrice = $v; }
    public function setDiscountRate(float $v): void { $this->discountRate = $v; }
    public function setTaxRate(float $v): void { $this->taxRate = $v; }
    public function setTotalPrice(float $v): void { $this->totalPrice = $v; }
    public function setReceivedQty(float $v): void { $this->receivedQty = $v; }

    public function isFullyReceived(): bool { return $this->receivedQty >= $this->quantity; }

    public function calculateTotal(): float
    {
        $discounted = $this->unitPrice * $this->quantity * (1 - $this->discountRate / 100);
        return $discounted * (1 + $this->taxRate / 100);
    }
}
