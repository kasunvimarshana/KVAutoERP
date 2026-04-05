<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\Entities;

class TaxGroupRate
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $taxGroupId,
        public readonly int $taxRateId,
        public readonly int $sortOrder,
    ) {}
}
