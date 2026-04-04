<?php

declare(strict_types=1);

namespace Modules\Product\Domain\ValueObjects;

use Modules\Core\Domain\Exceptions\DomainException;

class ProductType
{
    public const PHYSICAL = 'physical';
    public const SERVICE = 'service';
    public const DIGITAL = 'digital';
    public const COMBO = 'combo';
    public const VARIABLE = 'variable';

    public const VALID_TYPES = [self::PHYSICAL, self::SERVICE, self::DIGITAL, self::COMBO, self::VARIABLE];

    public static function assertValid(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new DomainException("Invalid product type: {$type}");
        }
    }
}
