<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

class Role
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $isSystem,
        public array $permissions = [],
    ) {}
}
