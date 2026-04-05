<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\Category;

interface CategoryServiceInterface
{
    public function getById(int $id): Category;

    public function getByTenant(int $tenantId): Collection;

    public function getTree(int $tenantId): Collection;

    public function getDescendants(int $categoryId): Collection;

    public function create(array $data): Category;

    public function update(int $id, array $data): Category;

    public function delete(int $id): bool;
}
