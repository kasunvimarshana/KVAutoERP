<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

class SystemConfig
{
    public function __construct(
        public readonly int $id,
        public ?int $tenantId,
        public string $key,
        public ?string $value,
        public string $group,
        public ?string $description,
        public bool $isSystem,
    ) {}
}
