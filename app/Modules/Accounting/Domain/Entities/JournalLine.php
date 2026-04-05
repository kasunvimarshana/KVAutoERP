<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class JournalLine
{
    public function __construct(
        private readonly string $id,
        private readonly string $journalEntryId,
        private readonly string $accountId,
        private readonly float $debit,
        private readonly float $credit,
        private readonly ?string $description,
        private readonly string $tenantId,
    ) {}

    public function getId(): string { return $this->id; }
    public function getJournalEntryId(): string { return $this->journalEntryId; }
    public function getAccountId(): string { return $this->accountId; }
    public function getDebit(): float { return $this->debit; }
    public function getCredit(): float { return $this->credit; }
    public function getDescription(): ?string { return $this->description; }
    public function getTenantId(): string { return $this->tenantId; }
}
