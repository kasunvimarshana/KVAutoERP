<?php
declare(strict_types=1);
namespace Modules\Category\Application\Services;

use Modules\Category\Application\Contracts\DeleteCategoryServiceInterface;
use Modules\Category\Domain\RepositoryInterfaces\CategoryRepositoryInterface;
use Modules\Core\Application\Services\BaseService;

class DeleteCategoryService extends BaseService implements DeleteCategoryServiceInterface
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): bool
    {
        return $this->repository->delete($data['id']);
    }
}
