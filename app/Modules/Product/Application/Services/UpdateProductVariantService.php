<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\UpdateProductVariantServiceInterface;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\Events\ProductVariantUpdated;
use Modules\Product\Domain\Repositories\ProductVariantRepositoryInterface;

class UpdateProductVariantService implements UpdateProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $repository,
    ) {}

    public function execute(int $id, array $data): ProductVariant
    {
        return DB::transaction(function () use ($id, $data): ProductVariant {
            $variant = $this->repository->update($id, $data);

            Event::dispatch(new ProductVariantUpdated($variant));

            return $variant;
        });
    }
}
