<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class Budget {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly int $fiscalYear,
        private readonly int $accountId,
        private readonly float $amount,
        private readonly string $period,  // monthly|quarterly|annual
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getFiscalYear(): int { return $this->fiscalYear; }
    public function getAccountId(): int { return $this->accountId; }
    public function getAmount(): float { return $this->amount; }
    public function getPeriod(): string { return $this->period; }
}
