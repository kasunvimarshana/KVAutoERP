<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;

final class Payment
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $paymentNumber,
        private readonly DateTimeImmutable $paymentDate,
        private readonly float $amount,
        private readonly string $currency,
        private readonly string $paymentMethod,
        private readonly ?string $fromAccountId,
        private readonly ?string $toAccountId,
        private readonly ?string $reference,
        private readonly ?string $notes,
        private readonly string $status,
        private readonly ?string $journalEntryId,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getPaymentNumber(): string { return $this->paymentNumber; }
    public function getPaymentDate(): DateTimeImmutable { return $this->paymentDate; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getFromAccountId(): ?string { return $this->fromAccountId; }
    public function getToAccountId(): ?string { return $this->toAccountId; }
    public function getReference(): ?string { return $this->reference; }
    public function getNotes(): ?string { return $this->notes; }
    public function getStatus(): string { return $this->status; }
    public function getJournalEntryId(): ?string { return $this->journalEntryId; }
}
