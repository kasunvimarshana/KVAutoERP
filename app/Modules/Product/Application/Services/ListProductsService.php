<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Application\Contracts\ListProductsServiceInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class ListProductsService implements ListProductsServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->findByTenant($tenantId, $perPage, $page);
    }
}
