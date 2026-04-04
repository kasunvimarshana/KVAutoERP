<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\ValueObjects;

enum TenantPlan: string
{
    case FREE = 'free';
    case STARTER = 'starter';
    case PROFESSIONAL = 'professional';
    case ENTERPRISE = 'enterprise';
}
