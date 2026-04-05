<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

final class Product
{
    public const TYPE_PHYSICAL = 'physical';
    public const TYPE_SERVICE = 'service';
    public const TYPE_DIGITAL = 'digital';
    public const TYPE_COMBO = 'combo';
    public const TYPE_VARIABLE = 'variable';
    public const TYPES = [
        self::TYPE_PHYSICAL,
        self::TYPE_SERVICE,
        self::TYPE_DIGITAL,
        self::TYPE_COMBO,
        self::TYPE_VARIABLE,
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DISCONTINUED = 'discontinued';
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DISCONTINUED,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly ?int $categoryId,
        public readonly string $name,
        public readonly string $sku,
        public readonly ?string $barcode,
        public readonly string $type,
        public readonly string $status,
        public readonly ?string $description,
        public readonly ?string $shortDescription,
        public readonly string $unitOfMeasure,
        public readonly ?float $weight,
        public readonly ?array $dimensions,
        public readonly ?array $images,
        public readonly ?array $attributes,
        public readonly ?string $taxClass,
        public readonly float $costPrice,
        public readonly float $sellingPrice,
        public readonly bool $isSerialized,
        public readonly bool $trackInventory,
        public readonly float $minStockLevel,
        public readonly ?float $maxStockLevel,
        public readonly float $reorderPoint,
        public readonly int $leadTimeDays,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isPhysical(): bool
    {
        return $this->type === self::TYPE_PHYSICAL;
    }

    public function isService(): bool
    {
        return $this->type === self::TYPE_SERVICE;
    }

    public function requiresInventory(): bool
    {
        return $this->isPhysical() && $this->trackInventory;
    }
}
