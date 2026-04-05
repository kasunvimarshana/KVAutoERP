<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;

final class Budget
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly int $fiscalYear,
        private readonly string $accountId,
        private readonly float $amount,
        private readonly string $period,
        private readonly DateTimeImmutable $startDate,
        private readonly DateTimeImmutable $endDate,
        private readonly ?string $notes,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getFiscalYear(): int { return $this->fiscalYear; }
    public function getAccountId(): string { return $this->accountId; }
    public function getAmount(): float { return $this->amount; }
    public function getPeriod(): string { return $this->period; }
    public function getStartDate(): DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): DateTimeImmutable { return $this->endDate; }
    public function getNotes(): ?string { return $this->notes; }
}
