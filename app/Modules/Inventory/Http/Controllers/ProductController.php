<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Http\Requests\StoreProductRequest;
use App\Modules\Inventory\Http\Requests\UpdateProductRequest;
use App\Modules\Inventory\Http\Resources\ProductCollection;
use App\Modules\Inventory\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProductController
 *
 * RESTful inventory product CRUD.
 * Thin controller – delegates all logic to InventoryService.
 */
class ProductController
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    /**
     * GET /api/v1/inventory/products
     */
    public function index(Request $request): JsonResponse
    {
        $pagination = $this->resolvePagination($request);

        $products = $this->inventoryService->list(
            filters: $request->only(['status', 'category']),
            sort:    $this->resolveSort($request),
            perPage: $pagination['per_page'],
            page:    $pagination['page']
        );

        return response()->json([
            'success' => true,
            'data'    => new ProductCollection($products),
        ]);
    }

    /**
     * GET /api/v1/inventory/products/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);
        $pagination = $this->resolvePagination($request);

        $products = $this->inventoryService->search(
            term:    $request->input('q'),
            filters: $request->only(['status', 'category']),
            sort:    $this->resolveSort($request),
            perPage: $pagination['per_page'],
            page:    $pagination['page']
        );

        return response()->json([
            'success' => true,
            'data'    => new ProductCollection($products),
        ]);
    }

    /**
     * GET /api/v1/inventory/products/{id}
     */
    public function show(int|string $id): JsonResponse
    {
        $product = $this->inventoryService->findById($id);

        return response()->json([
            'success' => true,
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * POST /api/v1/inventory/products
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->inventoryService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data'    => new ProductResource($product),
        ], 201);
    }

    /**
     * PUT /api/v1/inventory/products/{id}
     */
    public function update(UpdateProductRequest $request, int|string $id): JsonResponse
    {
        $product = $this->inventoryService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * DELETE /api/v1/inventory/products/{id}
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->inventoryService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * GET /api/v1/inventory/products/low-stock
     */
    public function lowStock(Request $request): JsonResponse
    {
        $pagination = $this->resolvePagination($request);
        $threshold  = (int) $request->input('threshold', 10);

        $products = $this->inventoryService->lowStockAlert($threshold, $pagination['per_page'], $pagination['page']);

        return response()->json([
            'success' => true,
            'data'    => new ProductCollection($products),
        ]);
    }

    // -------------------------------------------------------------------------
    //  Private helpers
    // -------------------------------------------------------------------------

    /** @return array{per_page: int|null, page: int} */
    private function resolvePagination(Request $request): array
    {
        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : null;
        $page    = max(1, (int) $request->input('page', 1));

        return ['per_page' => $perPage, 'page' => $page];
    }

    /** @return array<string,string> */
    private function resolveSort(Request $request): array
    {
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        return [$sortBy => $sortDir];
    }
}
