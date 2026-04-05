<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class CycleCount
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $countNumber,
        public readonly int $locationId,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $startedAt,
        public readonly ?\DateTimeImmutable $completedAt,
        public readonly ?int $createdBy,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
