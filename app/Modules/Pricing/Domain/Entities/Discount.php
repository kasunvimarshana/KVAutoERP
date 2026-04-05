<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Entities;

class Discount
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly string $code,
        public readonly string $type,
        public readonly float $value,
        public readonly string $appliesToType,
        public readonly ?int $appliesToId,
        public readonly ?float $minOrderAmount,
        public readonly ?\DateTimeImmutable $validFrom,
        public readonly ?\DateTimeImmutable $validTo,
        public readonly bool $isActive,
        public readonly ?int $usageLimit,
        public readonly int $usageCount,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
