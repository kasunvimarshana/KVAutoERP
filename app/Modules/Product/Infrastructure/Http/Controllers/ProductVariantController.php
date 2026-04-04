<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\CreateProductVariantServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductVariantServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductVariantServiceInterface;
use Modules\Product\Application\DTOs\CreateVariantData;
use Modules\Product\Domain\Repositories\ProductVariantRepositoryInterface;
use Modules\Product\Infrastructure\Http\Requests\CreateProductVariantRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductVariantRequest;
use Modules\Product\Infrastructure\Http\Resources\ProductVariantResource;

class ProductVariantController extends Controller
{
    public function __construct(
        private readonly CreateProductVariantServiceInterface $createService,
        private readonly UpdateProductVariantServiceInterface $updateService,
        private readonly DeleteProductVariantServiceInterface $deleteService,
        private readonly ProductVariantRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $productId = (int) $request->get('product_id', 0);
        $variants = $this->repository->findByProduct(
            $productId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => ProductVariantResource::collection($variants->items()),
            'meta' => [
                'current_page' => $variants->currentPage(),
                'last_page'    => $variants->lastPage(),
                'per_page'     => $variants->perPage(),
                'total'        => $variants->total(),
            ],
        ]);
    }

    public function store(CreateProductVariantRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateVariantData::fromArray([
            'tenantId'        => $validated['tenant_id'],
            'productId'       => $validated['product_id'],
            'name'            => $validated['name'],
            'sku'             => $validated['sku'],
            'barcode'         => $validated['barcode'] ?? null,
            'attributes'      => $validated['attributes'] ?? [],
            'price'           => $validated['price'] ?? null,
            'cost'            => $validated['cost'] ?? null,
            'weight'          => $validated['weight'] ?? null,
            'isActive'        => $validated['is_active'] ?? true,
            'stockManagement' => $validated['stock_management'] ?? true,
            'createdBy'       => $request->user()?->id,
        ]);

        $variant = $this->createService->execute($data);

        return response()->json(new ProductVariantResource($variant), 201);
    }

    public function show(int $id): JsonResponse
    {
        $variant = $this->repository->findById($id);
        if ($variant === null) {
            return response()->json(['message' => 'Variant not found.'], 404);
        }

        return response()->json(new ProductVariantResource($variant));
    }

    public function update(UpdateProductVariantRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $updateData = array_filter([
            'name'             => $validated['name'] ?? null,
            'sku'              => $validated['sku'] ?? null,
            'barcode'          => $validated['barcode'] ?? null,
            'attributes'       => $validated['attributes'] ?? null,
            'price'            => $validated['price'] ?? null,
            'cost'             => $validated['cost'] ?? null,
            'weight'           => $validated['weight'] ?? null,
            'is_active'        => $validated['is_active'] ?? null,
            'stock_management' => $validated['stock_management'] ?? null,
            'updated_by'       => $request->user()?->id,
        ], fn ($v) => $v !== null);

        $variant = $this->updateService->execute($id, $updateData);

        return response()->json(new ProductVariantResource($variant));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }
}
