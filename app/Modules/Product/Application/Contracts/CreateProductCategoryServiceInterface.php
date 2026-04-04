<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Application\DTOs\CreateProductCategoryData;
use Modules\Product\Domain\Entities\ProductCategory;

interface CreateProductCategoryServiceInterface
{
    public function execute(CreateProductCategoryData $data): ProductCategory;
}
