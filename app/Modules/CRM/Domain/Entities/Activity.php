<?php declare(strict_types=1);
namespace Modules\CRM\Domain\Entities;
class Activity {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $type,
        private readonly string $subject,
        private readonly ?string $description,
        private readonly int $relatedType,
        private readonly string $relatedEntityType,
        private readonly string $status,
        private readonly ?\DateTimeInterface $scheduledAt,
        private readonly ?\DateTimeInterface $completedAt,
        private readonly ?int $assignedTo,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getType(): string { return $this->type; }
    public function getSubject(): string { return $this->subject; }
    public function getDescription(): ?string { return $this->description; }
    public function getRelatedType(): int { return $this->relatedType; }
    public function getRelatedEntityType(): string { return $this->relatedEntityType; }
    public function getStatus(): string { return $this->status; }
    public function getScheduledAt(): ?\DateTimeInterface { return $this->scheduledAt; }
    public function getCompletedAt(): ?\DateTimeInterface { return $this->completedAt; }
    public function getAssignedTo(): ?int { return $this->assignedTo; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
}
