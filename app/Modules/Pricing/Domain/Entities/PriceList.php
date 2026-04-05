<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Entities;

class PriceList
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly string $code,
        public readonly string $currency,
        public readonly bool $isDefault,
        public readonly ?\DateTimeImmutable $validFrom,
        public readonly ?\DateTimeImmutable $validTo,
        public readonly ?string $description,
        public readonly bool $isActive,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
