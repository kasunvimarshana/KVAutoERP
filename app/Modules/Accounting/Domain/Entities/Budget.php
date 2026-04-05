<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class Budget
{
    public const PERIOD_MONTHLY   = 'monthly';
    public const PERIOD_QUARTERLY = 'quarterly';
    public const PERIOD_ANNUAL    = 'annual';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly int $accountId,
        public readonly string $periodType,
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate,
        public readonly float $amount,
        public readonly float $spent,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function getRemaining(): float
    {
        return $this->amount - $this->spent;
    }

    public function getUtilizationPct(): float
    {
        if ($this->amount === 0.0) {
            return 0.0;
        }

        return round(($this->spent / $this->amount) * 100, 2);
    }
}
