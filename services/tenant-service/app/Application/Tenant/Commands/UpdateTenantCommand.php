<?php

declare(strict_types=1);

namespace App\Application\Tenant\Commands;

final readonly class UpdateTenantCommand
{
    public function __construct(
        public ?string $name               = null,
        public ?string $slug               = null,
        public ?string $domain             = null,
        public ?string $status             = null,
        public ?string $plan               = null,
        public ?int    $maxUsers           = null,
        public ?int    $maxOrganizations   = null,
        public ?string $trialEndsAt        = null,
        public ?string $subscriptionEndsAt = null,
        public ?array  $settings           = null,
        public ?array  $config             = null,
        public ?array  $metadata           = null,
    ) {}
}
