<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class BankTransaction
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $bankAccountId,
        private readonly \DateTimeInterface $date,
        private readonly string $description,
        private readonly float $amount,
        private readonly string $type,
        private readonly string $status,
        private readonly ?string $category,
        private readonly ?int $accountId,
        private readonly ?string $referenceNo,
        private readonly string $source,
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

    public function getBankAccountId(): int
    {
        return $this->bankAccountId;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function getReferenceNo(): ?string
    {
        return $this->referenceNo;
    }

    public function getSource(): string
    {
        return $this->source;
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
