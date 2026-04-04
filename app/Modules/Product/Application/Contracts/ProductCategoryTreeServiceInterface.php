<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

interface ProductCategoryTreeServiceInterface
{
    public function execute(int $tenantId): array;
}
