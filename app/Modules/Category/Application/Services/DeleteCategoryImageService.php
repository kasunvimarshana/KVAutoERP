<?php
declare(strict_types=1);
namespace Modules\Category\Application\Services;

use Modules\Category\Application\Contracts\DeleteCategoryImageServiceInterface;
use Modules\Category\Domain\RepositoryInterfaces\CategoryImageRepositoryInterface;
use Modules\Core\Application\Services\BaseService;

class DeleteCategoryImageService extends BaseService implements DeleteCategoryImageServiceInterface
{
    public function __construct(CategoryImageRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): bool
    {
        return $this->repository->delete($data['id']);
    }
}
