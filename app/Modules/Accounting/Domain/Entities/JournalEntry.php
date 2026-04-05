<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class JournalEntry {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $reference,
        private readonly string $description,
        private readonly \DateTimeInterface $transactionDate,
        private readonly string $status, // draft|posted|voided
        private readonly string $currency,
        private readonly ?int $createdBy,
        private readonly ?\DateTimeInterface $postedAt,
        /** @var JournalLine[] */
        private array $lines = [],
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getReference(): string { return $this->reference; }
    public function getDescription(): string { return $this->description; }
    public function getTransactionDate(): \DateTimeInterface { return $this->transactionDate; }
    public function getStatus(): string { return $this->status; }
    public function getCurrency(): string { return $this->currency; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getPostedAt(): ?\DateTimeInterface { return $this->postedAt; }
    public function getLines(): array { return $this->lines; }
    public function setLines(array $lines): void { $this->lines = $lines; }
    public function isBalanced(): bool {
        $totalDebit = array_sum(array_map(fn($l) => $l->getDebitAmount(), $this->lines));
        $totalCredit = array_sum(array_map(fn($l) => $l->getCreditAmount(), $this->lines));
        return abs($totalDebit - $totalCredit) < PHP_FLOAT_EPSILON;
    }
    public function getTotalDebit(): float { return array_sum(array_map(fn($l) => $l->getDebitAmount(), $this->lines)); }
    public function getTotalCredit(): float { return array_sum(array_map(fn($l) => $l->getCreditAmount(), $this->lines)); }
}
