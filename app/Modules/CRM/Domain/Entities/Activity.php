<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

class Activity
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $relatedType,
        private readonly int $relatedId,
        private readonly string $type,
        private readonly string $subject,
        private readonly ?string $description,
        private readonly ?\DateTimeInterface $scheduledAt,
        private readonly ?\DateTimeInterface $completedAt,
        private readonly string $status,
        private readonly ?int $assignedTo,
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

    public function getRelatedType(): string
    {
        return $this->relatedType;
    }

    public function getRelatedId(): int
    {
        return $this->relatedId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAssignedTo(): ?int
    {
        return $this->assignedTo;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
