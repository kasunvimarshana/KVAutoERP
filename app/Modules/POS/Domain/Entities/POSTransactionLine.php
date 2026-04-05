<?php declare(strict_types=1);
namespace Modules\POS\Domain\Entities;
class POSTransactionLine {
    public function __construct(
        private readonly ?int $id,
        private readonly int $transactionId,
        private readonly int $productId,
        private readonly ?int $variantId,
        private readonly float $quantity,
        private readonly float $unitPrice,
        private readonly float $taxAmount,
        private readonly float $discountAmount,
        private readonly float $lineTotal,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTransactionId(): int { return $this->transactionId; }
    public function getProductId(): int { return $this->productId; }
    public function getVariantId(): ?int { return $this->variantId; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): float { return $this->unitPrice; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getLineTotal(): float { return $this->lineTotal; }
}
