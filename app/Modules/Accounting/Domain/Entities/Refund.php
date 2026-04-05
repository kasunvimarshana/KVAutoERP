<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;

final class Refund
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $refundNumber,
        private readonly DateTimeImmutable $refundDate,
        private readonly float $amount,
        private readonly string $currency,
        private readonly string $paymentMethod,
        private readonly ?string $accountId,
        private readonly ?string $reference,
        private readonly ?string $notes,
        private readonly string $status,
        private readonly ?string $originalPaymentId,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getRefundNumber(): string { return $this->refundNumber; }
    public function getRefundDate(): DateTimeImmutable { return $this->refundDate; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getAccountId(): ?string { return $this->accountId; }
    public function getReference(): ?string { return $this->reference; }
    public function getNotes(): ?string { return $this->notes; }
    public function getStatus(): string { return $this->status; }
    public function getOriginalPaymentId(): ?string { return $this->originalPaymentId; }
}
