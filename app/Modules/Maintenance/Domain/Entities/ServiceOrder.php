<?php declare(strict_types=1);
namespace Modules\Maintenance\Domain\Entities;

class ServiceOrder
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $code,
        private readonly string $type,
        private readonly int $assetId,
        private readonly ?int $assignedToId,
        private readonly string $priority,
        private readonly string $status,
        private readonly string $description,
        private readonly \DateTimeInterface $scheduledDate,
        private readonly ?\DateTimeInterface $completedAt,
        private readonly ?float $cost,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getCode(): string { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getAssetId(): int { return $this->assetId; }
    public function getAssignedToId(): ?int { return $this->assignedToId; }
    public function getPriority(): string { return $this->priority; }
    public function getStatus(): string { return $this->status; }
    public function getDescription(): string { return $this->description; }
    public function getScheduledDate(): \DateTimeInterface { return $this->scheduledDate; }
    public function getCompletedAt(): ?\DateTimeInterface { return $this->completedAt; }
    public function getCost(): ?float { return $this->cost; }

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isOverdue(): bool
    {
        return !$this->isCompleted() && $this->scheduledDate < new \DateTimeImmutable();
    }
}
