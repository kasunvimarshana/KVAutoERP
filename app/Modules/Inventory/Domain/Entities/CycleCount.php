<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\Entities;
class CycleCount {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $warehouseId,
        private readonly string $status, // pending|in_progress|completed|cancelled
        private readonly string $reference,
        private readonly ?\DateTimeInterface $scheduledAt,
        private readonly ?\DateTimeInterface $completedAt,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getWarehouseId(): int { return $this->warehouseId; }
    public function getStatus(): string { return $this->status; }
    public function getReference(): string { return $this->reference; }
    public function getScheduledAt(): ?\DateTimeInterface { return $this->scheduledAt; }
    public function getCompletedAt(): ?\DateTimeInterface { return $this->completedAt; }
}
