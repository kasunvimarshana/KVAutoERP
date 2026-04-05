<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findBySlug(int $tenantId, string $slug): ?Category;

    public function findByTenant(int $tenantId): Collection;

    public function getTree(int $tenantId): Collection;

    public function getDescendants(int $categoryId): Collection;

    public function create(array $data): Category;

    public function update(int $id, array $data): ?Category;

    public function delete(int $id): bool;
}
