<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\Entities;

class TaxRate
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly string $code,
        public readonly float $rate,
        public readonly string $type,
        public readonly bool $isCompound,
        public readonly bool $isActive,
        public readonly ?string $country,
        public readonly ?string $region,
        public readonly ?string $description,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
