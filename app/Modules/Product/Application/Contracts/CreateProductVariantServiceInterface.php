<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Application\DTOs\CreateVariantData;
use Modules\Product\Domain\Entities\ProductVariant;

interface CreateProductVariantServiceInterface
{
    public function execute(CreateVariantData $data): ProductVariant;
}
