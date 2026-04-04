<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\DTOs;

readonly class UpdateRoleData
{
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $description = null,
    ) {}
}
