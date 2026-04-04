<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Entities;

use DateTimeInterface;

class Tenant
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $domain,
        public ?string $database,
        public string $status,
        public string $plan,
        public string $locale,
        public string $timezone,
        public string $currency,
        public array $settings,
        public ?DateTimeInterface $trialEndsAt,
        public ?DateTimeInterface $suspendedAt,
        public ?DateTimeInterface $createdAt,
        public ?DateTimeInterface $updatedAt,
    ) {}
}
