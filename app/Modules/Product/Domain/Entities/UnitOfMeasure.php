<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

final class UnitOfMeasure
{
    public const TYPE_WEIGHT = 'weight';
    public const TYPE_LENGTH = 'length';
    public const TYPE_VOLUME = 'volume';
    public const TYPE_AREA = 'area';
    public const TYPE_COUNT = 'count';
    public const TYPE_TIME = 'time';
    public const TYPES = [
        self::TYPE_WEIGHT,
        self::TYPE_LENGTH,
        self::TYPE_VOLUME,
        self::TYPE_AREA,
        self::TYPE_COUNT,
        self::TYPE_TIME,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly string $abbreviation,
        public readonly string $type,
        public readonly float $baseUnitFactor,
        public readonly bool $isBaseUnit,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
