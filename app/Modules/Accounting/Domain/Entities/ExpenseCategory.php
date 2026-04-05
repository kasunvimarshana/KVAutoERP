<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class ExpenseCategory
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly int $accountId,
        public readonly ?int $parentId,
        public readonly ?string $color,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }
}
