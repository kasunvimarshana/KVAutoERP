<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class StockAdjustment
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_POSTED, self::STATUS_CANCELLED];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $warehouseId,
        public readonly ?int $locationId,
        public readonly string $referenceNo,
        public readonly \DateTimeImmutable $adjustmentDate,
        public readonly string $reason,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly ?int $createdBy,
        public readonly ?int $postedBy,
        public readonly ?\DateTimeImmutable $postedAt,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }
}
