<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class TransactionRule
{
    public const APPLY_TO_ALL    = 'all';
    public const APPLY_TO_DEBIT  = 'debit';
    public const APPLY_TO_CREDIT = 'credit';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        /** @var array<int, array{field: string, operator: string, value: string}> */
        public readonly array $conditions,
        public readonly int $accountId,
        public readonly string $applyTo,
        public readonly int $priority,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
