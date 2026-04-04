<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\ValueObjects;

use Modules\Core\Domain\Exceptions\DomainException;

class TenantPlan
{
    public const STARTER = 'starter';
    public const PROFESSIONAL = 'professional';
    public const ENTERPRISE = 'enterprise';

    public static function assertValid(string $plan): void
    {
        $valid = [self::STARTER, self::PROFESSIONAL, self::ENTERPRISE];
        if (!in_array($plan, $valid, true)) {
            throw new DomainException("Invalid tenant plan: {$plan}. Must be one of: " . implode(', ', $valid));
        }
    }
}
