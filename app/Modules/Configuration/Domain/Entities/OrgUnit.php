<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

class OrgUnit
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $name,
        public string $code,
        public string $type,
        public ?int $parentId,
        public ?string $description,
        public bool $isActive,
        public ?array $metadata,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
    ) {}
}
