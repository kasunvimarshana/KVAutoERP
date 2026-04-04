<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Entities;

class Setting
{
    public function __construct(
        public ?int $id,
        public int $tenantId,
        public string $group,
        public string $key,
        public ?string $value,
        public string $type,
        public ?string $description,
    ) {}
}
