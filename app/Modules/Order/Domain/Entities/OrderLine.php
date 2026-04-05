<?php declare(strict_types=1);
namespace Modules\Order\Domain\Entities;
class OrderLine {
    public function __construct(
        private readonly ?int $id,
        private readonly int $orderId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly float $quantity,
        private readonly float $unitPrice,
        private readonly float $taxAmount,
        private readonly float $discountAmount,
        private readonly float $lineTotal,
        private readonly ?string $batchNumber,
        private readonly ?string $lotNumber,
        private readonly ?string $serialNumber,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getOrderId(): int { return $this->orderId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getLineTotal(): float { return $this->lineTotal; }
    public function getBatchNumber(): ?string { return $this->batchNumber; }
    public function getLotNumber(): ?string { return $this->lotNumber; }
    public function getSerialNumber(): ?string { return $this->serialNumber; }
}
