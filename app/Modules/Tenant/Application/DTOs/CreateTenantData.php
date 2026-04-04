<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateTenantData extends BaseDto
{
    public string $name;
    public string $slug;
    public string $status = 'active';
    public string $plan = 'free';
    public ?array $settings = null;
    public ?array $metadata = null;
    public ?int $createdBy = null;
}
