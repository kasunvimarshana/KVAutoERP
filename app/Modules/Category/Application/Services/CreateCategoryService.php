<?php
declare(strict_types=1);
namespace Modules\Category\Application\Services;

use Modules\Category\Application\Contracts\CreateCategoryServiceInterface;
use Modules\Category\Domain\Entities\Category;
use Modules\Category\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Core\Application\Services\BaseService;

class CreateCategoryService extends BaseService implements CreateCategoryServiceInterface
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): Category
    {
        $category = new Category(
            tenantId:    $data['tenant_id'],
            name:        $data['name'],
            slug:        $data['slug'],
            description: $data['description'] ?? null,
            parentId:    $data['parent_id'] ?? null,
            depth:       $data['depth'] ?? 0,
            path:        $data['path'] ?? '',
            status:      $data['status'] ?? 'active',
            attributes:  $data['attributes'] ?? null,
            metadata:    $data['metadata'] ?? null,
        );
        return $this->repository->save($category);
    }
}
