<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class JournalLine
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $journalEntryId,
        private readonly int $accountId,
        private readonly ?string $description,
        private readonly float $debit,
        private readonly float $credit,
        private readonly array $metadata,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJournalEntryId(): int
    {
        return $this->journalEntryId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDebit(): float
    {
        return $this->debit;
    }

    public function getCredit(): float
    {
        return $this->credit;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
