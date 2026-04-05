<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class Refund
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly ?string $referenceNo,
        private readonly \DateTimeInterface $refundDate,
        private readonly float $amount,
        private readonly string $currency,
        private readonly string $paymentMethod,
        private readonly string $status,
        private readonly ?int $paymentId,
        private readonly ?string $reason,
        private readonly int $accountId,
        private readonly ?string $notes,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getReferenceNo(): ?string
    {
        return $this->referenceNo;
    }

    public function getRefundDate(): \DateTimeInterface
    {
        return $this->refundDate;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
