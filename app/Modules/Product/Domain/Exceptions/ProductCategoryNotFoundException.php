<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Exceptions;

use RuntimeException;

class ProductCategoryNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Product category with ID {$id} not found.");
    }
}
