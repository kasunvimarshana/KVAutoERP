<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class Budget
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly int $accountId,
        private readonly int $year,
        private readonly ?int $month,
        private readonly float $amount,
        private readonly float $spent,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getSpent(): float
    {
        return $this->spent;
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
