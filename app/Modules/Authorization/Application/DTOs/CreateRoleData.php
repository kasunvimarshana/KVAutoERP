<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\DTOs;

readonly class CreateRoleData
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public bool $isSystem = false,
    ) {}
}
