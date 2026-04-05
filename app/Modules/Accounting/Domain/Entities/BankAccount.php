<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class BankAccount
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly ?string $accountNumber,
        private readonly string $accountType,
        private readonly string $currency,
        private readonly float $balance,
        private readonly ?int $linkedAccountId,
        private readonly bool $isActive,
        private readonly ?\DateTimeInterface $lastSyncedAt,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getLinkedAccountId(): ?int
    {
        return $this->linkedAccountId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getLastSyncedAt(): ?\DateTimeInterface
    {
        return $this->lastSyncedAt;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
