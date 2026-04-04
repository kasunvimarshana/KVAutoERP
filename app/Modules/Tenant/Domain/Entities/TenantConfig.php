<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

class TenantConfig
{
    public function __construct(
        public readonly int $id,
        public int $tenantId,
        public string $key,
        public ?string $value,
        public string $group,
        public bool $isSecret,
    ) {}
}
