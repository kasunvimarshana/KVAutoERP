<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

class Category
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $name,
        public readonly string $code,
        public readonly ?int $parentId,
        public readonly string $path,
        public readonly int $level,
        public readonly ?string $description,
        public readonly bool $isActive,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
