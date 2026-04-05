<?php declare(strict_types=1);
namespace Modules\CRM\Domain\Entities;
class Lead {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $title,
        private readonly int $contactId,
        private readonly string $status,
        private readonly float $value,
        private readonly string $currency,
        private readonly ?int $assignedTo,
        private readonly ?\DateTimeInterface $expectedCloseDate,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getTitle(): string { return $this->title; }
    public function getContactId(): int { return $this->contactId; }
    public function getStatus(): string { return $this->status; }
    public function getValue(): float { return $this->value; }
    public function getCurrency(): string { return $this->currency; }
    public function getAssignedTo(): ?int { return $this->assignedTo; }
    public function getExpectedCloseDate(): ?\DateTimeInterface { return $this->expectedCloseDate; }
    public function isConverted(): bool { return $this->status === 'converted'; }
}
