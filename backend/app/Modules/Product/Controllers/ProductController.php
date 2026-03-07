<?php

namespace App\Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Requests\CreateProductRequest;
use App\Modules\Product\Requests\UpdateProductRequest;
use App\Modules\Product\Resources\ProductResource;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'category', 'is_active', 'min_price', 'max_price', 'sort_by', 'sort_dir', 'tenant_id']);
        $perPage = $request->input('per_page', 15);
        $products = $this->productService->list($filters, $perPage);
        return ProductResource::collection($products);
    }

    public function show(int $id): ProductResource
    {
        $product = $this->productService->get($id);
        return new ProductResource($product);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = $this->productService->create($dto);
        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = $this->productService->update($id, $dto);
        return new ProductResource($product);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productService->delete($id);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
