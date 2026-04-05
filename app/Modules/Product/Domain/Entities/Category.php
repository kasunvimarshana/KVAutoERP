<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Entities;

final class Category
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly ?int $parentId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly ?string $image,
        public readonly bool $isActive,
        public readonly string $path,
        public readonly int $level,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }
}
