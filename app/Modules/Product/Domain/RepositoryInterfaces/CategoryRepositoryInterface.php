<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findBySlug(int $tenantId, string $slug): ?Category;

    /** @return array<int, array{category: Category, children: array}> */
    public function getTree(int $tenantId): array;

    public function create(array $data): Category;

    public function update(int $id, array $data): ?Category;

    public function delete(int $id): bool;
}
