<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;

final class JournalEntry
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $entryNumber,
        private readonly DateTimeImmutable $date,
        private readonly string $description,
        private readonly ?string $reference,
        private readonly string $status,
        private readonly float $totalDebit,
        private readonly float $totalCredit,
        private readonly ?string $createdBy,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getEntryNumber(): string { return $this->entryNumber; }
    public function getDate(): DateTimeImmutable { return $this->date; }
    public function getDescription(): string { return $this->description; }
    public function getReference(): ?string { return $this->reference; }
    public function getStatus(): string { return $this->status; }
    public function getTotalDebit(): float { return $this->totalDebit; }
    public function getTotalCredit(): float { return $this->totalCredit; }
    public function getCreatedBy(): ?string { return $this->createdBy; }

    public function isBalanced(): bool
    {
        return abs($this->totalDebit - $this->totalCredit) < 0.0001;
    }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isPosted(): bool { return $this->status === 'posted'; }
    public function isVoided(): bool { return $this->status === 'voided'; }
}
