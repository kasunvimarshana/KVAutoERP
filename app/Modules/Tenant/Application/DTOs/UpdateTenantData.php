<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateTenantData extends BaseDto
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $status = null;
    public ?string $plan = null;
    public ?array $settings = null;
    public ?array $metadata = null;
    public ?int $updatedBy = null;
}
