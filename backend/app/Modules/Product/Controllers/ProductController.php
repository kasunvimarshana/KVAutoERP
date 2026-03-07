<?php

namespace App\Modules\Product\Controllers;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Requests\CreateProductRequest;
use App\Modules\Product\Requests\UpdateProductRequest;
use App\Modules\Product\Resources\ProductResource;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = app('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $filters  = $request->only(['category', 'is_active', 'search', 'min_price', 'max_price']);

        return ProductResource::collection(
            $this->productService->list($tenantId, $perPage, $filters)
        );
    }

    public function show(string $id): ProductResource
    {
        $tenantId = app('tenant_id');
        return new ProductResource($this->productService->findById($id, $tenantId));
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $tenantId = app('tenant_id');
        $data     = $request->validated();
        $data['tenant_id'] = $tenantId;

        $product = $this->productService->create(ProductDTO::fromRequest($data));

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, string $id): ProductResource
    {
        $tenantId = app('tenant_id');
        $product  = $this->productService->update($id, $tenantId, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = app('tenant_id');
        $this->productService->delete($id, $tenantId);

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function restore(string $id): JsonResponse
    {
        $this->productService->restore($id);

        return response()->json(['message' => 'Product restored successfully']);
    }
}
