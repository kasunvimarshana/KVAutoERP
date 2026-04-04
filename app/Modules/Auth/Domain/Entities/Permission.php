<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

class Permission
{
    public function __construct(
        public readonly int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public string $module,
        public string $action,
    ) {}
}
