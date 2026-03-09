<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Inventory\Commands\CreateProductCommand;
use App\Application\Inventory\Commands\DeleteProductCommand;
use App\Application\Inventory\Commands\UpdateProductCommand;
use App\Application\Inventory\Queries\GetProductQuery;
use App\Application\Inventory\Queries\ListProductsQuery;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $query    = new ListProductsQuery(
            tenantId: $tenantId,
            filters: $request->only(['status', 'category_id']),
            sorts: $request->input('sort') ? [$request->input('sort') => $request->input('direction', 'asc')] : ['created_at' => 'desc'],
            perPage: (int) $request->input('per_page', 20),
            page: (int) $request->input('page', 1),
            search: $request->input('search'),
            categoryId: $request->input('category_id'),
            status: $request->input('status'),
            minPrice: $request->input('min_price') !== null ? (float) $request->input('min_price') : null,
            maxPrice: $request->input('max_price') !== null ? (float) $request->input('max_price') : null,
            lowStockOnly: (bool) $request->input('low_stock', false),
            outOfStockOnly: (bool) $request->input('out_of_stock', false),
        );
        $result = $this->inventoryService->listProducts($query);
        if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return response()->json(new ProductCollection($result));
        }
        return response()->json(['data' => array_map(fn ($p) => (new ProductResource($p))->toArray($request), $result)]);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $product  = $this->inventoryService->getProduct($id, $tenantId);
        return response()->json(['data' => (new ProductResource($product))->toArray($request)]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $command   = new CreateProductCommand(
            tenantId: $tenantId,
            sku: strtoupper($validated['sku']),
            name: $validated['name'],
            description: $validated['description'] ?? '',
            categoryId: $validated['category_id'] ?? null,
            price: (float) $validated['price'],
            costPrice: (float) ($validated['cost_price'] ?? 0),
            currency: strtoupper($validated['currency'] ?? 'USD'),
            stockQuantity: (int) $validated['stock_quantity'],
            minStockLevel: (int) ($validated['min_stock_level'] ?? 0),
            maxStockLevel: (int) ($validated['max_stock_level'] ?? 0),
            unit: $validated['unit'] ?? 'unit',
            barcode: $validated['barcode'] ?? null,
            tags: $validated['tags'] ?? [],
            attributes: $validated['attributes'] ?? [],
            performedBy: $request->get('_auth_user_id', 'system'),
            status: $validated['status'] ?? 'active',
        );
        $product = $this->inventoryService->createProduct($command);
        return response()->json(['data' => (new ProductResource($product))->toArray($request)], 201);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $command   = new UpdateProductCommand(
            productId: $id,
            tenantId: $tenantId,
            performedBy: $request->get('_auth_user_id', 'system'),
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            price: isset($validated['price']) ? (float) $validated['price'] : null,
            costPrice: isset($validated['cost_price']) ? (float) $validated['cost_price'] : null,
            currency: $validated['currency'] ?? null,
            minStockLevel: isset($validated['min_stock_level']) ? (int) $validated['min_stock_level'] : null,
            maxStockLevel: isset($validated['max_stock_level']) ? (int) $validated['max_stock_level'] : null,
            unit: $validated['unit'] ?? null,
            barcode: $validated['barcode'] ?? null,
            tags: $validated['tags'] ?? null,
            attributes: $validated['attributes'] ?? null,
            status: $validated['status'] ?? null,
            isActive: isset($validated['is_active']) ? (bool) $validated['is_active'] : null,
        );
        $product = $this->inventoryService->updateProduct($command);
        return response()->json(['data' => (new ProductResource($product))->toArray($request)]);
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $this->inventoryService->deleteProduct(new DeleteProductCommand(
            productId: $id,
            tenantId: $tenantId,
            performedBy: $request->get('_auth_user_id', 'system'),
        ));
        return response()->json(['message' => 'Product deleted.'], 200);
    }
}
