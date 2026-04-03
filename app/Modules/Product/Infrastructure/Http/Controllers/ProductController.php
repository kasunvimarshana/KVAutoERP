<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Product\Application\Contracts\BulkUploadProductImagesServiceInterface;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Application\Contracts\FindProductServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Infrastructure\Http\Requests\StoreProductRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductRequest;
class ProductController extends AuthorizedController
{
    public function __construct(
        private FindProductServiceInterface $findService,
        private CreateProductServiceInterface $createService,
        private UpdateProductServiceInterface $updateService,
        private DeleteProductServiceInterface $deleteService,
        private BulkUploadProductImagesServiceInterface $bulkUploadService,
    ) {}
    public function index(): JsonResponse { return response()->json([]); }
    public function show(int $id): JsonResponse { return response()->json([]); }
    public function store(StoreProductRequest $request): JsonResponse { return response()->json([]); }
    public function update(UpdateProductRequest $request, int $id): JsonResponse { return response()->json([]); }
    public function destroy(int $id): JsonResponse { return response()->json([]); }
}
