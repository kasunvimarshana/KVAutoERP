<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\ImageStorageStrategyInterface;
use Modules\Product\Application\Contracts\UploadProductImageServiceInterface;
use Modules\Product\Domain\Entities\ProductImage;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
class UploadProductImageService extends BaseService implements UploadProductImageServiceInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        ProductImageRepositoryInterface $repository,
        private ImageStorageStrategyInterface $storageStrategy,
    ) {
        parent::__construct($repository);
    }

    protected function handle(array $data): mixed
    {
        $product  = $this->productRepository->find($data['product_id']);
        $file     = $data['file'];
        $filePath = $this->storageStrategy->store($file, 'products/' . $product->getId());

        $image = new ProductImage(
            tenantId:   $product->getTenantId(),
            productId:  $product->getId(),
            uuid:       (string) \Illuminate\Support\Str::uuid(),
            name:       $file->getClientOriginalName(),
            filePath:   $filePath,
            mimeType:   $file->getMimeType(),
            size:       $file->getSize(),
            sortOrder:  $data['sort_order'] ?? 0,
            isPrimary:  $data['is_primary'] ?? false,
            metadata:   $data['metadata'] ?? [],
        );

        return $this->repository->save($image);
    }
}
