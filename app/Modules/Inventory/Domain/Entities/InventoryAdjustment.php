<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class InventoryAdjustment
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $adjustmentNumber,
        public readonly \DateTimeImmutable $date,
        public readonly int $locationId,
        public readonly string $status,
        public readonly string $reason,
        public readonly ?string $notes,
        public readonly ?int $createdBy,
        public readonly ?int $approvedBy,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
