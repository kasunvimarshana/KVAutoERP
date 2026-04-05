<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\ProductVariant;

interface ProductVariantServiceInterface
{
    public function getById(int $id): ProductVariant;

    public function getByProduct(int $productId): Collection;

    public function create(array $data): ProductVariant;

    public function update(int $id, array $data): ProductVariant;

    public function delete(int $id): bool;
}
