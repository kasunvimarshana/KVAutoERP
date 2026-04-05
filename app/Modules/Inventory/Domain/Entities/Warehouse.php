<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class Warehouse
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly string $code,
        public readonly ?array $address,
        public readonly bool $isActive,
        public readonly bool $isDefault,
        public readonly ?int $managerId,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
