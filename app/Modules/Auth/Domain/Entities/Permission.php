<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

final class Permission
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $guardName,
        public readonly string $module,
        public readonly string $action,
    ) {}

    public function toSlug(): string
    {
        return "{$this->module}.{$this->action}";
    }
}
