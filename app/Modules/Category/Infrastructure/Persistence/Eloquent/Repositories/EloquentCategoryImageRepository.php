<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Category\Domain\Entities\CategoryImage;
use Modules\Category\Domain\RepositoryInterfaces\CategoryImageRepositoryInterface;
use Modules\Category\Infrastructure\Persistence\Eloquent\Models\CategoryImageModel;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;

class EloquentCategoryImageRepository extends EloquentRepository implements CategoryImageRepositoryInterface
{
    public function __construct(CategoryImageModel $model)
    {
        parent::__construct($model);
    }

    public function findByUuid(string $uuid): ?CategoryImage { return null; }
    public function findByCategory(int $categoryId): ?CategoryImage { return null; }
    public function save(CategoryImage $image): CategoryImage { return $image; }
    public function deleteByCategory(int $categoryId): bool { return true; }
}
