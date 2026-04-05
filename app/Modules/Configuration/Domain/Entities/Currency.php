<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

final class Currency
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $symbol,
        public readonly int $decimalPlaces,
        public readonly bool $isDefault,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
