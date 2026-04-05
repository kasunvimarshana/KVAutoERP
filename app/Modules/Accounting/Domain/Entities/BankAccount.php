<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class BankAccount {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $accountType, // checking|savings|credit_card|line_of_credit|paypal|other
        private readonly string $currency,
        private readonly ?int $glAccountId,
        private readonly float $openingBalance,
        private readonly bool $isActive,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getAccountType(): string { return $this->accountType; }
    public function getCurrency(): string { return $this->currency; }
    public function getGlAccountId(): ?int { return $this->glAccountId; }
    public function getOpeningBalance(): float { return $this->openingBalance; }
    public function isActive(): bool { return $this->isActive; }
}
