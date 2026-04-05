<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

class Lead
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly ?int $contactId,
        private readonly string $name,
        private readonly ?string $email,
        private readonly ?string $phone,
        private readonly string $source,
        private readonly string $status,
        private readonly float $value,
        private readonly string $currency,
        private readonly ?int $assignedTo,
        private readonly int $probability,
        private readonly ?\DateTimeInterface $expectedCloseDate,
        private readonly ?string $notes,
        private readonly array $metadata,
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

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAssignedTo(): ?int
    {
        return $this->assignedTo;
    }

    public function getProbability(): int
    {
        return $this->probability;
    }

    public function getExpectedCloseDate(): ?\DateTimeInterface
    {
        return $this->expectedCloseDate;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['won', 'lost'], true);
    }
}
