<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class CycleCount
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $warehouseId,
        public readonly ?int $locationId,
        public readonly string $referenceNo,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $scheduledDate,
        public readonly ?\DateTimeImmutable $completedDate,
        public readonly ?int $createdBy,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
