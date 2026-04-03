<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\BulkUploadProductImagesServiceInterface;
use Modules\Product\Application\Contracts\ImageStorageStrategyInterface;
use Modules\Product\Domain\Entities\ProductImage;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
class BulkUploadProductImagesService extends BaseService implements BulkUploadProductImagesServiceInterface
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
        $product    = $this->productRepository->find($data['product_id']);
        $files      = $data['files'] ?? [];
        $sortStart  = $data['sort_order_start'] ?? 0;
        $primaryIdx = $data['is_primary_index'] ?? null;
        $saved      = [];

        foreach ($files as $idx => $file) {
            $filePath = $this->storageStrategy->store($file, 'products/' . $product->getId());
            $image    = new ProductImage(
                tenantId:  $product->getTenantId(),
                productId: $product->getId(),
                uuid:      (string) \Illuminate\Support\Str::uuid(),
                name:      $file->getClientOriginalName(),
                filePath:  $filePath,
                mimeType:  $file->getMimeType(),
                size:      $file->getSize(),
                sortOrder: $sortStart + $idx,
                isPrimary: $primaryIdx === $idx,
            );
            $saved[] = $this->repository->save($image);
        }

        return $saved;
    }
}
