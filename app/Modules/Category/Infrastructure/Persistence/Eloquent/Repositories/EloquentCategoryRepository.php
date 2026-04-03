<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Category\Domain\Entities\Category;
use Modules\Category\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Category\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;

class EloquentCategoryRepository extends EloquentRepository implements CategoryRepositoryInterface
{
    public function __construct(CategoryModel $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(int $tenantId, string $slug): ?Category { return null; }
    public function findByTenant(int $tenantId): Collection { return new Collection(); }
    public function findChildren(int $parentId): Collection { return new Collection(); }
    public function findRoots(int $tenantId): Collection { return new Collection(); }
    public function getTree(int $tenantId): Collection { return new Collection(); }
    public function getDescendants(int $categoryId): Collection { return new Collection(); }
    public function save(Category $category): Category { return $category; }
}
