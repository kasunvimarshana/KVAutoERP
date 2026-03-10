<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

class ProductNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Product with ID {$id} not found.", 404);
    }
}
