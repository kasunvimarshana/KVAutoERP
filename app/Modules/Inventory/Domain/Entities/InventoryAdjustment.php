<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class InventoryAdjustment
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly int $warehouseId,
        private readonly string $status,
        private readonly ?string $reason,
        private readonly ?int $adjustedBy,
        private readonly ?int $approvedBy,
        private readonly ?\DateTimeInterface $appliedAt,
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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getAdjustedBy(): ?int
    {
        return $this->adjustedBy;
    }

    public function getApprovedBy(): ?int
    {
        return $this->approvedBy;
    }

    public function getAppliedAt(): ?\DateTimeInterface
    {
        return $this->appliedAt;
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
