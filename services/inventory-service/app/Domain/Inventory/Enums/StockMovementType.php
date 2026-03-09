<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Enums;

/**
 * Represents all possible stock movement types.
 */
enum StockMovementType: string
{
    case IN          = 'in';
    case OUT         = 'out';
    case ADJUSTMENT  = 'adjustment';
    case RESERVATION = 'reservation';
    case RELEASE     = 'release';
    case DAMAGE      = 'damage';
    case RETURN      = 'return';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::IN          => 'Stock In',
            self::OUT         => 'Stock Out',
            self::ADJUSTMENT  => 'Adjustment',
            self::RESERVATION => 'Reservation',
            self::RELEASE     => 'Release',
            self::DAMAGE      => 'Damage Write-off',
            self::RETURN      => 'Customer Return',
        };
    }

    /**
     * Whether the movement increases stock.
     */
    public function increasesStock(): bool
    {
        return match ($this) {
            self::IN, self::RELEASE, self::RETURN => true,
            default => false,
        };
    }

    /**
     * Whether the movement decreases stock.
     */
    public function decreasesStock(): bool
    {
        return match ($this) {
            self::OUT, self::RESERVATION, self::DAMAGE => true,
            default => false,
        };
    }
}
