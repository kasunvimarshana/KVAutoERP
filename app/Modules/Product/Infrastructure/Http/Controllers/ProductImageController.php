<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Controllers;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Product\Application\Contracts\BulkUploadProductImagesServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductImageServiceInterface;
use Modules\Product\Application\Contracts\FindProductImagesServiceInterface;
use Modules\Product\Application\Contracts\ImageStorageStrategyInterface;
use Modules\Product\Application\Contracts\UploadProductImageServiceInterface;
class ProductImageController extends AuthorizedController
{
    public function __construct(
        private UploadProductImageServiceInterface $uploadService,
        private BulkUploadProductImagesServiceInterface $bulkUploadService,
        private DeleteProductImageServiceInterface $deleteService,
        private FindProductImagesServiceInterface $findService,
        private ImageStorageStrategyInterface $storageStrategy,
    ) {}

    public function store(): void {}
    public function storeMany(): void {}
    public function destroy(): void {}
}
