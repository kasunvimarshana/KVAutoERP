<?php

declare(strict_types=1);

namespace Modules\User\Domain\ValueObjects;

use Modules\Core\Domain\Exceptions\DomainException;

class UserStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const LOCKED = 'locked';

    public static function assertValid(string $status): void
    {
        $valid = [self::ACTIVE, self::INACTIVE, self::LOCKED];
        if (!in_array($status, $valid, true)) {
            throw new DomainException("Invalid user status: {$status}. Must be one of: " . implode(', ', $valid));
        }
    }
}
