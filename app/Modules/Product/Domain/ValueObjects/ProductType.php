<?php

declare(strict_types=1);

namespace Modules\Product\Domain\ValueObjects;

enum ProductType: string
{
    case Physical = 'physical';
    case Service = 'service';
    case Digital = 'digital';
    case Combo = 'combo';
    case Variable = 'variable';
}
