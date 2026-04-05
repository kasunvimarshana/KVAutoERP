<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

class StockLocation
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $warehouseId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $type,
        public readonly ?int $parentId,
        public readonly string $path,
        public readonly int $level,
        public readonly bool $isActive,
        public readonly ?array $metadata,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
