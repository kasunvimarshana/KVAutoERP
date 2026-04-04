<?php

declare(strict_types=1);

namespace Modules\Product\Domain\ValueObjects;

use Modules\Core\Domain\Exceptions\DomainException;

class ProductStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const DISCONTINUED = 'discontinued';

    public const VALID_STATUSES = [self::ACTIVE, self::INACTIVE, self::DISCONTINUED];

    public static function assertValid(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new DomainException("Invalid product status: {$status}");
        }
    }
}
