<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Contracts\DeleteProductVariantServiceInterface;
use Modules\Product\Domain\Repositories\ProductVariantRepositoryInterface;

class DeleteProductVariantService implements DeleteProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}
