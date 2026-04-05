<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class BankTransaction {
    public function __construct(
        private readonly ?int $id,
        private readonly int $bankAccountId,
        private readonly int $tenantId,
        private readonly string $type,       // debit|credit
        private readonly float $amount,
        private readonly \DateTimeInterface $transactionDate,
        private readonly string $description,
        private readonly string $status,     // pending|categorized|reconciled|excluded
        private readonly string $source,     // manual|import|api
        private readonly ?int $accountId,    // GL account for categorization
        private readonly ?string $reference,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getBankAccountId(): int { return $this->bankAccountId; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getType(): string { return $this->type; }
    public function getAmount(): float { return $this->amount; }
    public function getTransactionDate(): \DateTimeInterface { return $this->transactionDate; }
    public function getDescription(): string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getSource(): string { return $this->source; }
    public function getAccountId(): ?int { return $this->accountId; }
    public function getReference(): ?string { return $this->reference; }
    public function isCategorized(): bool { return in_array($this->status, ['categorized','reconciled']); }
}
