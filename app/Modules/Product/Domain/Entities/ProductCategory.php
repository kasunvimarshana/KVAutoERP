<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class ProductCategory
{
    public function __construct(
        public ?int $id,
        public int $tenantId,
        public ?int $parentId,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $image,
        public bool $isActive,
        public int $sortOrder,
        public array $children = [],
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}
}
