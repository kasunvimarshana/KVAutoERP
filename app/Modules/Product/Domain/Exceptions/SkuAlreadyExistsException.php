<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Exceptions;

use RuntimeException;

class SkuAlreadyExistsException extends RuntimeException
{
    public function __construct(string $sku)
    {
        parent::__construct("SKU '{$sku}' already exists.");
    }
}
