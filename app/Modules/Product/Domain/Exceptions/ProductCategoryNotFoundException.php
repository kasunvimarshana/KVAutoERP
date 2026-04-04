<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class ProductCategoryNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("ProductCategory with id '{$id}' not found", 404);
    }
}
