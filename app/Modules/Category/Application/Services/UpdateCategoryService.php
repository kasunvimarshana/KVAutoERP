<?php
declare(strict_types=1);
namespace Modules\Category\Application\Services;

use Modules\Category\Application\Contracts\UpdateCategoryServiceInterface;
use Modules\Category\Domain\Exceptions\CategoryNotFoundException;
use Modules\Category\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Core\Application\Services\BaseService;

class UpdateCategoryService extends BaseService implements UpdateCategoryServiceInterface
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): mixed
    {
        $category = $this->repository->find($data['id']);
        if (!$category) throw new CategoryNotFoundException($data['id']);
        $category->updateDetails(
            $data['name'] ?? $category->getName(),
            $data['slug'] ?? $category->getSlug(),
            $data['description'] ?? null,
            $data['parent_id'] ?? null,
            $data['path'] ?? '',
            $data['depth'] ?? 0,
            $data['attributes'] ?? null,
            $data['metadata'] ?? null,
        );
        return $this->repository->save($category);
    }
}
