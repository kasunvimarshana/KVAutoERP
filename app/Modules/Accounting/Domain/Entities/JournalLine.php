<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class JournalLine {
    public function __construct(
        private readonly ?int $id,
        private readonly int $journalEntryId,
        private readonly int $accountId,
        private readonly float $debitAmount,
        private readonly float $creditAmount,
        private readonly ?string $description,
    ) {
        if ($debitAmount < 0 || $creditAmount < 0) throw new \InvalidArgumentException("Amounts must be non-negative");
        if (!(abs($debitAmount) < PHP_FLOAT_EPSILON) && !(abs($creditAmount) < PHP_FLOAT_EPSILON)) {
            throw new \InvalidArgumentException("A journal line cannot have both debit and credit");
        }
    }
    public function getId(): ?int { return $this->id; }
    public function getJournalEntryId(): int { return $this->journalEntryId; }
    public function getAccountId(): int { return $this->accountId; }
    public function getDebitAmount(): float { return $this->debitAmount; }
    public function getCreditAmount(): float { return $this->creditAmount; }
    public function getDescription(): ?string { return $this->description; }
}
