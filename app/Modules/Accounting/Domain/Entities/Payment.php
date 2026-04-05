<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class Payment
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly ?string $referenceNo,
        private readonly \DateTimeInterface $paymentDate,
        private readonly float $amount,
        private readonly string $currency,
        private readonly string $paymentMethod,
        private readonly string $status,
        private readonly ?string $partyType,
        private readonly ?int $partyId,
        private readonly int $accountId,
        private readonly ?string $notes,
        private readonly array $metadata,
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

    public function getPaymentDate(): \DateTimeInterface
    {
        return $this->paymentDate;
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

    public function getPartyType(): ?string
    {
        return $this->partyType;
    }

    public function getPartyId(): ?int
    {
        return $this->partyId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
