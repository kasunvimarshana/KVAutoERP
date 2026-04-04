<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Application\DTOs\UpdateCategoryData;
use Modules\Product\Domain\Entities\ProductCategory;

interface UpdateProductCategoryServiceInterface
{
    public function execute(int $id, UpdateCategoryData $data): ProductCategory;
}
