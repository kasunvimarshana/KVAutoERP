<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Modules\Product\Domain\Entities\Category;

interface CategoryServiceInterface
{
    public function create(array $data): Category;

    public function update(int $id, array $data): Category;

    public function delete(int $id): bool;

    public function find(int $id): Category;

    public function getTree(int $tenantId): array;

    public function move(int $id, ?int $newParentId): Category;
}
