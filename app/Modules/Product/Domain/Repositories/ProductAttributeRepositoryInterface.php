<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\ProductAttribute;

interface ProductAttributeRepositoryInterface
{
    public function findById(int $id): ?ProductAttribute;

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function create(array $data): ProductAttribute;

    public function update(int $id, array $data): ProductAttribute;

    public function delete(int $id): bool;
}
