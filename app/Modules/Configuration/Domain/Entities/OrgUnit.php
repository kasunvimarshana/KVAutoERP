<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

class OrgUnit
{
    public function __construct(
        public ?int $id,
        public int $tenantId,
        public ?int $parentId,
        public string $name,
        public ?string $code,
        public string $type,
        public ?string $description,
        public bool $isActive,
        public int $sortOrder,
        public array $children = [],
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}
}
