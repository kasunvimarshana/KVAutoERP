<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\ProductComponent;

interface ProductComponentRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?ProductComponent;

    public function findByProduct(int $productId, int $tenantId): array;

    public function create(array $data): ProductComponent;

    public function update(int $id, array $data): ProductComponent;

    public function delete(int $id, int $tenantId): bool;
}
