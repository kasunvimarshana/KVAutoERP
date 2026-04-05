<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class BankAccount
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly ?string $accountNumber,
        private readonly ?string $bankName,
        private readonly string $accountType,
        private readonly float $balance,
        private readonly string $currency,
        private readonly bool $isActive,
        private readonly ?string $chartOfAccountId,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getAccountNumber(): ?string { return $this->accountNumber; }
    public function getBankName(): ?string { return $this->bankName; }
    public function getAccountType(): string { return $this->accountType; }
    public function getBalance(): float { return $this->balance; }
    public function getCurrency(): string { return $this->currency; }
    public function isActive(): bool { return $this->isActive; }
    public function getChartOfAccountId(): ?string { return $this->chartOfAccountId; }
}
