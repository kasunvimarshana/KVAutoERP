<?php

declare(strict_types=1);

namespace App\Application\Organization\Commands;

final readonly class CreateOrganizationCommand
{
    public function __construct(
        public string  $tenantId,
        public string  $name,
        public string  $slug,
        public ?string $parentId    = null,
        public ?string $description = null,
        public string  $status      = 'active',
        public array   $settings    = [],
        public array   $metadata    = [],
    ) {}
}
