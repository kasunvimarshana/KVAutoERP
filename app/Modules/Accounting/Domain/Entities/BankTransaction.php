<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;

final class BankTransaction
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $bankAccountId,
        private readonly DateTimeImmutable $date,
        private readonly string $description,
        private readonly float $amount,
        private readonly string $type,
        private readonly string $status,
        private readonly string $source,
        private readonly ?string $categoryId,
        private readonly ?string $journalEntryId,
        private readonly ?string $reference,
        private readonly ?array $metadata,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getBankAccountId(): string { return $this->bankAccountId; }
    public function getDate(): DateTimeImmutable { return $this->date; }
    public function getDescription(): string { return $this->description; }
    public function getAmount(): float { return $this->amount; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getSource(): string { return $this->source; }
    public function getCategoryId(): ?string { return $this->categoryId; }
    public function getJournalEntryId(): ?string { return $this->journalEntryId; }
    public function getReference(): ?string { return $this->reference; }
    public function getMetadata(): ?array { return $this->metadata; }
}
