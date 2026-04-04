<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Contracts\DeleteProductCategoryServiceInterface;
use Modules\Product\Domain\Exceptions\ProductCategoryNotFoundException;
use Modules\Product\Domain\Repositories\ProductCategoryRepositoryInterface;

class DeleteProductCategoryService implements DeleteProductCategoryServiceInterface
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $existing = $this->repository->findById($id);
            if ($existing === null) {
                throw new ProductCategoryNotFoundException($id);
            }

            $this->repository->deleteNode($id);
        });
    }
}
