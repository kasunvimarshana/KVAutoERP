<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Entities;

class OrderTransaction
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $orderId,
        private readonly string $type,          // payment|refund
        private readonly float $amount,
        private readonly string $currency,
        private readonly string $paymentMethod, // cash|bank_transfer|credit_card|check|other
        private readonly string $status,        // pending|completed|failed
        private readonly ?string $referenceNo,
        private readonly ?string $notes,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getOrderId(): int { return $this->orderId; }
    public function getType(): string { return $this->type; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getStatus(): string { return $this->status; }
    public function getReferenceNo(): ?string { return $this->referenceNo; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}
