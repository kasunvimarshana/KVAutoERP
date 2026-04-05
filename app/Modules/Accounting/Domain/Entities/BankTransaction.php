<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class BankTransaction
{
    public const TYPE_DEBIT  = 'debit';
    public const TYPE_CREDIT = 'credit';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_IMPORT = 'import';
    public const SOURCE_API    = 'api';

    public const STATUS_PENDING      = 'pending';
    public const STATUS_CATEGORIZED  = 'categorized';
    public const STATUS_RECONCILED   = 'reconciled';
    public const STATUS_EXCLUDED     = 'excluded';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $bankAccountId,
        public readonly \DateTimeImmutable $date,
        public readonly float $amount,
        public readonly string $type,
        public readonly string $description,
        public readonly ?string $reference,
        public readonly string $source,
        public readonly string $status,
        public readonly ?int $accountId,
        public readonly ?int $transactionRuleId,
        public readonly ?array $metadata,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isCategorized(): bool
    {
        return $this->status === self::STATUS_CATEGORIZED
            || $this->status === self::STATUS_RECONCILED;
    }

    public function isReconciled(): bool
    {
        return $this->status === self::STATUS_RECONCILED;
    }
}
