<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\DTOs;

readonly class UpdateTenantData
{
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $plan = null,
        public ?string $locale = null,
        public ?string $timezone = null,
        public ?string $currency = null,
        public ?string $status = null,
    ) {}
}
