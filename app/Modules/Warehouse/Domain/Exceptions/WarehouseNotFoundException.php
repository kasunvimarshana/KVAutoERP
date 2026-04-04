<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Exceptions;

use RuntimeException;

class WarehouseNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Warehouse with ID {$id} not found.");
    }
}
