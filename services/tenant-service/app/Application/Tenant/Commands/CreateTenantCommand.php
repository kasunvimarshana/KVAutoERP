<?php

declare(strict_types=1);

namespace App\Application\Tenant\Commands;

final readonly class CreateTenantCommand
{
    public function __construct(
        public string  $name,
        public string  $slug,
        public string  $plan,
        public ?string $domain            = null,
        public string  $status            = 'pending',
        public int     $maxUsers          = 100,
        public int     $maxOrganizations  = 10,
        public ?string $trialEndsAt       = null,
        public array   $settings          = [],
        public array   $config            = [],
        public array   $databaseConfig    = [],
        public array   $mailConfig        = [],
        public array   $cacheConfig       = [],
        public array   $brokerConfig      = [],
        public array   $metadata          = [],
    ) {}
}
