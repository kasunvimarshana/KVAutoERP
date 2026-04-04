<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\ProductVariant;

interface UpdateProductVariantServiceInterface
{
    public function execute(int $id, array $data): ProductVariant;
}
