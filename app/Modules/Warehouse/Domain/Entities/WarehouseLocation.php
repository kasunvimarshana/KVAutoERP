<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Entities;

class WarehouseLocation
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public int $warehouseId,
        public ?int $parentId,
        public string $name,
        public string $code,
        public string $type,
        public ?string $barcode,
        public ?float $capacity,
        public bool $isActive,
        public int $level,
        public ?string $path,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
    ) {}
}
