<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\ProductCategory;

interface GetProductCategoryServiceInterface
{
    public function execute(int $id): ProductCategory;
}
