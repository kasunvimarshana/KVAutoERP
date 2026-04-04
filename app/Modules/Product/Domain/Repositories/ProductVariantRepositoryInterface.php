<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantRepositoryInterface
{
    public function findById(int $id): ?ProductVariant;

    public function findByProduct(int $productId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function create(array $data): ProductVariant;

    public function update(int $id, array $data): ProductVariant;

    public function delete(int $id): bool;
}
