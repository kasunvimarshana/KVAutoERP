<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Domain\Events\ProductDeleted;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class DeleteProductService implements DeleteProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $product = $this->repository->findById($id);
            if ($product === null) {
                throw new ProductNotFoundException($id);
            }

            $tenantId = $product->tenantId;
            $this->repository->delete($id);

            Event::dispatch(new ProductDeleted($id, $tenantId));
        });
    }
}
