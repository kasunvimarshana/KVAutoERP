<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\CategoryServiceInterface;
use Modules\Product\Domain\Entities\Category;
use Modules\Product\Domain\RepositoryInterfaces\CategoryRepositoryInterface;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository,
    ) {}

    public function create(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->repository->update($id, $data);

        if ($category === null) {
            throw new NotFoundException('Category', $id);
        }

        return $category;
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function find(int $id): Category
    {
        $category = $this->repository->findById($id);

        if ($category === null) {
            throw new NotFoundException('Category', $id);
        }

        return $category;
    }

    public function getTree(int $tenantId): array
    {
        return $this->repository->getTree($tenantId);
    }

    public function move(int $id, ?int $newParentId): Category
    {
        $category = $this->find($id);

        $level = 0;
        $path  = '/';

        if ($newParentId !== null) {
            $parent = $this->find($newParentId);
            $level  = $parent->getLevel() + 1;
            $path   = rtrim($parent->getPath(), '/') . '/' . $newParentId;
        }

        return $this->update($id, [
            'parent_id' => $newParentId,
            'level'     => $level,
            'path'      => $path . '/' . $category->getId(),
        ]);
    }
}
