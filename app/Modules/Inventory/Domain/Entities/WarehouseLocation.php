<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Entities;

final class WarehouseLocation
{
    public const TYPE_ZONE = 'zone';
    public const TYPE_AISLE = 'aisle';
    public const TYPE_RACK = 'rack';
    public const TYPE_SHELF = 'shelf';
    public const TYPE_BIN = 'bin';
    public const TYPES = [self::TYPE_ZONE, self::TYPE_AISLE, self::TYPE_RACK, self::TYPE_SHELF, self::TYPE_BIN];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $warehouseId,
        public readonly ?int $parentId,
        public readonly string $name,
        public readonly string $code,
        public readonly string $type,
        public readonly string $path,
        public readonly int $level,
        public readonly ?string $barcode,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }
}
