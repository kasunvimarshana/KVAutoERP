<?php declare(strict_types=1);
namespace Modules\POS\Domain\Entities;
class POSTransaction {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $sessionId,
        private readonly string $transactionNumber,
        private readonly float $subtotal,
        private readonly float $taxAmount,
        private readonly float $discountAmount,
        private readonly float $totalAmount,
        private readonly float $amountPaid,
        private readonly float $change,
        private readonly string $paymentMethod,
        private readonly string $status,
        /** @var POSTransactionLine[] */
        private array $lines = [],
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getSessionId(): int { return $this->sessionId; }
    public function getTransactionNumber(): string { return $this->transactionNumber; }
    public function getSubtotal(): float { return $this->subtotal; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getDiscountAmount(): float { return $this->discountAmount; }
    public function getTotalAmount(): float { return $this->totalAmount; }
    public function getAmountPaid(): float { return $this->amountPaid; }
    public function getChange(): float { return $this->change; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getStatus(): string { return $this->status; }
    public function getLines(): array { return $this->lines; }
    public function setLines(array $lines): void { $this->lines = $lines; }
}
