<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Category;

    public function findByCode(string $code, int $tenantId): ?Category;

    public function allByTenant(int $tenantId): array;

    public function getTree(int $tenantId): array;

    public function getDescendants(int $id, int $tenantId): array;

    public function create(array $data): Category;

    public function update(int $id, array $data): Category;

    public function delete(int $id, int $tenantId): bool;
}
