<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\ProductComponent;

interface ProductComponentRepositoryInterface
{
    public function findByProduct(int $productId): Collection;

    public function addComponent(array $data): ProductComponent;

    public function removeComponent(int $id): bool;

    public function update(int $id, array $data): ?ProductComponent;
}
