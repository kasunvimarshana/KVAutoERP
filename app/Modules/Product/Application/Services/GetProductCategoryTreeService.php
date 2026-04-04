<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Product\Application\Contracts\GetProductCategoryTreeServiceInterface;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;

class GetProductCategoryTreeService implements GetProductCategoryTreeServiceInterface
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId): array
    {
        return $this->repository->getTree($tenantId);
    }
}
