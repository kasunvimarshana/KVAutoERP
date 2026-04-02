<?php

declare(strict_types=1);

namespace Modules\StockMovement\Domain\ValueObjects;

class MovementType
{
    const RECEIPT    = 'receipt';
    const ISSUE      = 'issue';
    const TRANSFER   = 'transfer';
    const ADJUSTMENT = 'adjustment';
    const RETURN_IN  = 'return_in';
    const RETURN_OUT = 'return_out';

    public static function values(): array
    {
        return ['receipt', 'issue', 'transfer', 'adjustment', 'return_in', 'return_out'];
    }
}
