<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class Account
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $code,
        private readonly string $name,
        private readonly string $type,
        private readonly ?string $parentId,
        private readonly bool $isActive,
        private readonly float $openingBalance,
        private readonly float $currentBalance,
        private readonly string $currency,
        private readonly ?string $description,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function getParentId(): ?string { return $this->parentId; }
    public function isActive(): bool { return $this->isActive; }
    public function getOpeningBalance(): float { return $this->openingBalance; }
    public function getCurrentBalance(): float { return $this->currentBalance; }
    public function getCurrency(): string { return $this->currency; }
    public function getDescription(): ?string { return $this->description; }

    public function isDebitNormal(): bool
    {
        return in_array($this->type, ['asset', 'expense'], true);
    }

    public function isCreditNormal(): bool
    {
        return in_array($this->type, ['liability', 'equity', 'income', 'accounts_payable', 'accounts_receivable'], true);
    }
}
