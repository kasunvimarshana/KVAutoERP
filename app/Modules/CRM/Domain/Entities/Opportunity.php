<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

class Opportunity
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly ?int $leadId,
        private readonly ?int $contactId,
        private readonly string $name,
        private readonly string $stage,
        private readonly float $value,
        private readonly string $currency,
        private readonly int $probability,
        private readonly ?\DateTimeInterface $expectedCloseDate,
        private readonly ?int $assignedTo,
        private readonly ?string $description,
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

    public function getLeadId(): ?int
    {
        return $this->leadId;
    }

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getProbability(): int
    {
        return $this->probability;
    }

    public function getExpectedCloseDate(): ?\DateTimeInterface
    {
        return $this->expectedCloseDate;
    }

    public function getAssignedTo(): ?int
    {
        return $this->assignedTo;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isWon(): bool
    {
        return $this->stage === 'closed_won';
    }

    public function isLost(): bool
    {
        return $this->stage === 'closed_lost';
    }
}
