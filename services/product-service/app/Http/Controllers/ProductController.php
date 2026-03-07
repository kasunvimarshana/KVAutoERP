<?php

namespace App\Http\Controllers;

use App\DTOs\ProductDTO;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $page     = (int) $request->query('page', 1);

        $filters = array_filter([
            'search'    => $request->query('search'),
            'category'  => $request->query('category'),
            'status'    => $request->query('status'),
            'low_stock' => $request->boolean('low_stock'),
            'sort_by'   => $request->query('sort_by'),
            'sort_dir'  => $request->query('sort_dir'),
        ], fn ($v) => $v !== null && $v !== false && $v !== '');

        $paginator = $this->productService->listProducts($tenantId, $filters, $perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => collect($paginator->items())->map(
                fn ($p) => ProductDTO::fromModel($p)->toArray()
            ),
            'message' => 'Products retrieved successfully.',
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $dto      = $this->productService->getProduct($tenantId, $id);

        if ($dto === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Product not found.',
                'meta'    => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Product retrieved successfully.',
            'meta'    => [],
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        try {
            $dto = $this->productService->createProduct($tenantId, $request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Validation failed.',
                'meta'    => ['errors' => $e->errors()],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Product created successfully.',
            'meta'    => [],
        ], 201);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        try {
            $dto = $this->productService->updateProduct($tenantId, $id, $request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Validation failed.',
                'meta'    => ['errors' => $e->errors()],
            ], 422);
        }

        if ($dto === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Product not found.',
                'meta'    => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Product updated successfully.',
            'meta'    => [],
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $deleted  = $this->productService->deleteProduct($tenantId, $id);

        return response()->json([
            'success' => $deleted,
            'data'    => null,
            'message' => $deleted ? 'Product deleted successfully.' : 'Product not found.',
            'meta'    => [],
        ], $deleted ? 200 : 404);
    }

    public function getBySku(Request $request, string $sku): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $dto      = $this->productService->getProductBySku($tenantId, $sku);

        if ($dto === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Product not found.',
                'meta'    => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Product retrieved successfully.',
            'meta'    => [],
        ]);
    }

    public function internalIndex(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $ids      = $request->query('ids');
        $perPage  = (int) $request->query('per_page', 100);

        $repo = app(\App\Repositories\ProductRepository::class)->withTenant($tenantId);

        if (!empty($ids)) {
            $idArray  = explode(',', $ids);
            $products = $repo->newQuery()->whereIn('id', $idArray)->get();
            return response()->json([
                'success' => true,
                'data'    => $products->map(fn ($p) => ProductDTO::fromModel($p)->toArray()),
                'message' => 'Products retrieved.',
                'meta'    => [],
            ]);
        }

        $paginator = $repo->getWithPagination($perPage);
        return response()->json([
            'success' => true,
            'data'    => collect($paginator->items())->map(fn ($p) => ProductDTO::fromModel($p)->toArray()),
            'message' => 'Products retrieved.',
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
