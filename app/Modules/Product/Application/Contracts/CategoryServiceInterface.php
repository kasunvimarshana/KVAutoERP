<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\Category;

interface CategoryServiceInterface
{
    public function createCategory(array $data): Category;

    public function updateCategory(int $id, array $data): Category;

    public function deleteCategory(int $id, int $tenantId): bool;

    public function getCategory(int $id, int $tenantId): Category;

    public function getAll(int $tenantId): array;

    public function getTree(int $tenantId): array;

    public function getDescendants(int $id, int $tenantId): array;
}
