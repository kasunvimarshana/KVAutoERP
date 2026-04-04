<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Exceptions;

use RuntimeException;

class LocationNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Warehouse location with ID {$id} not found.");
    }
}
