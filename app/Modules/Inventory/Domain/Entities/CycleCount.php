<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class CycleCount
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $warehouseId,
        private readonly string $status,
        private readonly ?\DateTimeInterface $startedAt,
        private readonly ?\DateTimeInterface $completedAt,
        private readonly ?int $createdBy,
        private readonly ?string $notes,
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

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
