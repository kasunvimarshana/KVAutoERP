<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class JournalEntry
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $referenceNo,
        private readonly string $description,
        private readonly \DateTimeInterface $entryDate,
        private readonly string $status,
        private readonly array $lines,
        private readonly ?int $createdBy,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getReferenceNo(): string
    {
        return $this->referenceNo;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getEntryDate(): \DateTimeInterface
    {
        return $this->entryDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /** @return JournalLine[] */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
