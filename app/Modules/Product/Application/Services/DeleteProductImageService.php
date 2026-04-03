<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\DeleteProductImageServiceInterface;
use Modules\Product\Application\Contracts\ImageStorageStrategyInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
class DeleteProductImageService extends BaseService implements DeleteProductImageServiceInterface
{
    public function __construct(
        ProductImageRepositoryInterface $repository,
        private ImageStorageStrategyInterface $storageStrategy,
    ) {
        parent::__construct($repository);
    }

    protected function handle(array $data): bool
    {
        $image = $this->repository->find($data['image_id']);
        $this->storageStrategy->delete($image->getFilePath());
        return $this->repository->delete($image->getId());
    }
}
