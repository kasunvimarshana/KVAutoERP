<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductAttribute
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $name,
        public string $slug,
        public string $type,
        public ?array $options,
    ) {}
}
