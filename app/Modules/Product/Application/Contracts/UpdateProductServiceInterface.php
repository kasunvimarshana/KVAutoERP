<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Application\DTOs\UpdateProductData;
use Modules\Product\Domain\Entities\Product;

interface UpdateProductServiceInterface
{
    public function execute(int $id, UpdateProductData $data): Product;
}
