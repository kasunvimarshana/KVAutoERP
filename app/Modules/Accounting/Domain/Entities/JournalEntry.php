<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class JournalEntry
{
    public const STATUS_DRAFT  = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_VOIDED = 'voided';

    public const TYPE_MANUAL     = 'manual';
    public const TYPE_PURCHASE   = 'purchase';
    public const TYPE_SALE       = 'sale';
    public const TYPE_PAYMENT    = 'payment';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_BANK       = 'bank';
    public const TYPE_PAYROLL    = 'payroll';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $referenceNo,
        public readonly \DateTimeImmutable $date,
        public readonly string $description,
        public readonly string $status,
        public readonly string $type,
        public readonly ?int $createdBy,
        public readonly ?\DateTimeImmutable $postedAt,
        public readonly ?\DateTimeImmutable $voidedAt,
        public readonly ?string $voidReason,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    public function isVoidable(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }
}
