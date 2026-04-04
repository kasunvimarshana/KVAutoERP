<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Entities\ProductCategory;

interface ProductCategoryRepositoryInterface
{
    public function findById(int $id): ?ProductCategory;

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function insertNode(array $data, ?int $parentId): ProductCategory;

    public function updateNode(int $id, array $data): ProductCategory;

    public function deleteNode(int $id): bool;

    public function getTree(int $tenantId): array;

    public function getDescendants(int $id): array;
}
