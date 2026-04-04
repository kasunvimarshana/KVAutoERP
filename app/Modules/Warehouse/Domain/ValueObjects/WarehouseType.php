<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\ValueObjects;

enum WarehouseType: string
{
    case Standard = 'standard';
    case Bonded = 'bonded';
    case Cold = 'cold';
    case Virtual = 'virtual';
}
