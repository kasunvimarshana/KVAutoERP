<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\ValueObjects;

use Modules\Core\Domain\Exceptions\DomainException;

class TenantStatus
{
    public const ACTIVE = 'active';
    public const SUSPENDED = 'suspended';
    public const CANCELLED = 'cancelled';

    public static function assertValid(string $status): void
    {
        $valid = [self::ACTIVE, self::SUSPENDED, self::CANCELLED];
        if (!in_array($status, $valid, true)) {
            throw new DomainException("Invalid tenant status: {$status}. Must be one of: " . implode(', ', $valid));
        }
    }
}
