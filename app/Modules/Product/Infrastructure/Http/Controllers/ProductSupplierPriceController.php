<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Product\Application\Contracts\CreateProductSupplierPriceServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductSupplierPriceServiceInterface;
use Modules\Product\Application\Contracts\FindProductSupplierPriceServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductSupplierPriceServiceInterface;
use Modules\Product\Domain\Entities\ProductSupplierPrice;
use Modules\Product\Infrastructure\Http\Requests\ListProductSupplierPriceRequest;
use Modules\Product\Infrastructure\Http\Requests\StoreProductSupplierPriceRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductSupplierPriceRequest;
use Modules\Product\Infrastructure\Http\Resources\ProductSupplierPriceCollection;
use Modules\Product\Infrastructure\Http\Resources\ProductSupplierPriceResource;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductSupplierPriceController extends AuthorizedController
{
    public function __construct(
        protected CreateProductSupplierPriceServiceInterface $createProductSupplierPriceService,
        protected UpdateProductSupplierPriceServiceInterface $updateProductSupplierPriceService,
        protected DeleteProductSupplierPriceServiceInterface $deleteProductSupplierPriceService,
        protected FindProductSupplierPriceServiceInterface $findProductSupplierPriceService,
    ) {}

    public function index(ListProductSupplierPriceRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ProductSupplierPrice::class);
        $validated = $request->validated();

        $items = $this->findProductSupplierPriceService->list(
            filters: [],
            perPage: (int) ($validated['per_page'] ?? 15),
            page: (int) ($validated['page'] ?? 1),
        );

        return (new ProductSupplierPriceCollection($items))->response();
    }

    public function store(StoreProductSupplierPriceRequest $request): JsonResponse
    {
        $this->authorize('create', ProductSupplierPrice::class);

        $item = $this->createProductSupplierPriceService->execute($request->validated());

        return (new ProductSupplierPriceResource($item))
            ->response()
            ->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function show(int $productSupplierPrice): ProductSupplierPriceResource
    {
        $item = $this->findOrFail($productSupplierPrice);
        $this->authorize('view', $item);

        return new ProductSupplierPriceResource($item);
    }

    public function update(UpdateProductSupplierPriceRequest $request, int $productSupplierPrice): ProductSupplierPriceResource
    {
        $item = $this->findOrFail($productSupplierPrice);
        $this->authorize('update', $item);

        $payload = $request->validated();
        $payload['id'] = $productSupplierPrice;

        $updated = $this->updateProductSupplierPriceService->execute($payload);

        return new ProductSupplierPriceResource($updated);
    }

    public function destroy(int $productSupplierPrice): JsonResponse
    {
        $item = $this->findOrFail($productSupplierPrice);
        $this->authorize('delete', $item);

        $this->deleteProductSupplierPriceService->execute(['id' => $productSupplierPrice]);

        return response()->json(['message' => 'ProductSupplierPrice deleted successfully']);
    }

    private function findOrFail(int $id): ProductSupplierPrice
    {
        $item = $this->findProductSupplierPriceService->find($id);

        if (! $item) {
            throw new NotFoundHttpException('ProductSupplierPrice not found.');
        }

        return $item;
    }
}
