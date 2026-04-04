<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\Product;

interface GetProductServiceInterface
{
    public function execute(int $id): Product;
}
