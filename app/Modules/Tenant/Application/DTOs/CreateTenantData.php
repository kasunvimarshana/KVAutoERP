<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\DTOs;

readonly class CreateTenantData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $plan,
        public string $locale = 'en',
        public string $timezone = 'UTC',
        public string $currency = 'USD',
    ) {}
}
