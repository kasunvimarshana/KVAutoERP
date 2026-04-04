<?php

declare(strict_types=1);

namespace Modules\Product\Domain\ValueObjects;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Discontinued = 'discontinued';
}
