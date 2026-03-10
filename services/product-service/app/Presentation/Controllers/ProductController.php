<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Contracts\Services\ProductServiceInterface;
use App\Application\DTOs\ProductDTO;
use App\Presentation\Requests\CreateProductRequest;
use App\Presentation\Requests\UpdateProductRequest;
use App\Presentation\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $params = array_merge($request->query(), ['tenant_id' => $tenantId]);
        $products = $this->productService->getAllProducts($params);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $product = $this->productService->getProduct($id, $tenantId);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $data = array_merge($request->validated(), ['tenant_id' => $tenantId]);
        $product = $this->productService->createProduct(ProductDTO::fromArray($data));

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $product = $this->productService->updateProduct($id, $request->validated(), $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $this->productService->deleteProduct($id, $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $query = $request->input('q', '');
        $products = $this->productService->searchProducts($query, $tenantId, $request->query());

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'query' => $query,
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        $products = $this->productService->getProductsByCategory($categoryId, $request->query());

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'category_id' => $categoryId,
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
