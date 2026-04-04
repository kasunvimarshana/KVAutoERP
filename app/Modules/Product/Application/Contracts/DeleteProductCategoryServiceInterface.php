<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

interface DeleteProductCategoryServiceInterface
{
    public function execute(int $id): void;
}
