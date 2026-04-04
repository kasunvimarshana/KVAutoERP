<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Application\DTOs\CreateProductData;
use Modules\Product\Domain\Entities\Product;

interface CreateProductServiceInterface
{
    public function execute(CreateProductData $data): Product;
}
