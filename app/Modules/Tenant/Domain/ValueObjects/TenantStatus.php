<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\ValueObjects;

enum TenantStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
}
