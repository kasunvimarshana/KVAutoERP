<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Application\Contracts\ListProductsServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Application\DTOs\CreateProductData;
use Modules\Product\Application\DTOs\UpdateProductData;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Infrastructure\Http\Requests\CreateProductRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductRequest;
use Modules\Product\Infrastructure\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function __construct(
        private readonly CreateProductServiceInterface $createService,
        private readonly UpdateProductServiceInterface $updateService,
        private readonly DeleteProductServiceInterface $deleteService,
        private readonly ListProductsServiceInterface $listService,
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $products = $this->listService->execute(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateProductData::fromArray([
            'tenantId'         => $validated['tenant_id'],
            'name'             => $validated['name'],
            'sku'              => $validated['sku'],
            'barcode'          => $validated['barcode'] ?? null,
            'type'             => $validated['type'] ?? 'physical',
            'status'           => $validated['status'] ?? 'active',
            'categoryId'       => $validated['category_id'] ?? null,
            'description'      => $validated['description'] ?? null,
            'shortDescription' => $validated['short_description'] ?? null,
            'weight'           => $validated['weight'] ?? null,
            'dimensions'       => $validated['dimensions'] ?? null,
            'images'           => $validated['images'] ?? null,
            'tags'             => $validated['tags'] ?? null,
            'isTaxable'        => $validated['is_taxable'] ?? true,
            'taxClass'         => $validated['tax_class'] ?? null,
            'hasSerial'        => $validated['has_serial'] ?? false,
            'hasBatch'         => $validated['has_batch'] ?? false,
            'hasLot'           => $validated['has_lot'] ?? false,
            'isSerialized'     => $validated['is_serialized'] ?? false,
            'createdBy'        => $request->user()?->id,
        ]);

        $product = $this->createService->execute($data);

        return response()->json(new ProductResource($product), 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->repository->findById($id);
        if ($product === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $data = UpdateProductData::fromArray([
                'name'             => $validated['name'] ?? null,
                'sku'              => $validated['sku'] ?? null,
                'barcode'          => $validated['barcode'] ?? null,
                'type'             => $validated['type'] ?? null,
                'status'           => $validated['status'] ?? null,
                'categoryId'       => $validated['category_id'] ?? null,
                'description'      => $validated['description'] ?? null,
                'shortDescription' => $validated['short_description'] ?? null,
                'weight'           => $validated['weight'] ?? null,
                'dimensions'       => $validated['dimensions'] ?? null,
                'images'           => $validated['images'] ?? null,
                'tags'             => $validated['tags'] ?? null,
                'isTaxable'        => $validated['is_taxable'] ?? null,
                'taxClass'         => $validated['tax_class'] ?? null,
                'hasSerial'        => $validated['has_serial'] ?? null,
                'hasBatch'         => $validated['has_batch'] ?? null,
                'hasLot'           => $validated['has_lot'] ?? null,
                'isSerialized'     => $validated['is_serialized'] ?? null,
                'updatedBy'        => $request->user()?->id,
            ]);

            $product = $this->updateService->execute($id, $data);

            return response()->json(new ProductResource($product));
        } catch (ProductNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteService->execute($id);

            return response()->json(null, 204);
        } catch (ProductNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
