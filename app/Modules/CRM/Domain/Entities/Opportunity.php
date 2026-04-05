<?php declare(strict_types=1);
namespace Modules\CRM\Domain\Entities;
class Opportunity {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $title,
        private readonly int $contactId,
        private readonly string $stage,
        private readonly float $value,
        private readonly string $currency,
        private readonly float $probability,
        private readonly ?int $assignedTo,
        private readonly ?\DateTimeInterface $expectedCloseDate,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getTitle(): string { return $this->title; }
    public function getContactId(): int { return $this->contactId; }
    public function getStage(): string { return $this->stage; }
    public function getValue(): float { return $this->value; }
    public function getCurrency(): string { return $this->currency; }
    public function getProbability(): float { return $this->probability; }
    public function getAssignedTo(): ?int { return $this->assignedTo; }
    public function getExpectedCloseDate(): ?\DateTimeInterface { return $this->expectedCloseDate; }
    public function getWeightedValue(): float { return $this->value * ($this->probability / 100.0); }
}
