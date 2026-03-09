<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Enums;

/**
 * Product lifecycle status.
 */
enum ProductStatus: string
{
    case ACTIVE       = 'active';
    case INACTIVE     = 'inactive';
    case DISCONTINUED = 'discontinued';
    case DRAFT        = 'draft';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE       => 'Active',
            self::INACTIVE     => 'Inactive',
            self::DISCONTINUED => 'Discontinued',
            self::DRAFT        => 'Draft',
        };
    }

    /**
     * Whether the product is visible / available for order.
     */
    public function isAvailable(): bool
    {
        return $this === self::ACTIVE;
    }
}
