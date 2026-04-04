<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\ValueObjects;

enum LocationType: string
{
    case Zone = 'zone';
    case Aisle = 'aisle';
    case Rack = 'rack';
    case Shelf = 'shelf';
    case Bin = 'bin';
}
