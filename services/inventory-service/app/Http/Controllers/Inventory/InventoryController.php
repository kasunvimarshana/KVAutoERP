<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Domain\Inventory\Services\InventoryService;
use App\Http\Requests\Inventory\CreateInventoryItemRequest;
use App\Http\Requests\Inventory\ListInventoryRequest;
use App\Http\Requests\Inventory\ReserveStockRequest;
use App\Http\Requests\Inventory\UpdateInventoryItemRequest;
use App\Http\Resources\Inventory\InventoryCollection;
use App\Http\Resources\Inventory\InventoryItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inventory Controller.
 *
 * Thin controller delegating all logic to InventoryService.
 */
class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * List inventory items with filtering, sorting, and conditional pagination.
     *
     * GET /api/v1/inventory
     */
    public function index(ListInventoryRequest $request): JsonResponse
    {
        $items = $this->inventoryService->list(
            tenantId: $request->attributes->get('tenant_id'),
            params: $request->validated(),
        );

        return (new InventoryCollection($items))->response();
    }

    /**
     * Get a single inventory item.
     *
     * GET /api/v1/inventory/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $item = $this->inventoryService->getById(
            id: $id,
            tenantId: $request->attributes->get('tenant_id'),
        );

        return (new InventoryItemResource($item->load(['category', 'warehouse'])))->response();
    }

    /**
     * Create a new inventory item.
     *
     * POST /api/v1/inventory
     */
    public function store(CreateInventoryItemRequest $request): JsonResponse
    {
        $item = $this->inventoryService->create(
            tenantId: $request->attributes->get('tenant_id'),
            data: $request->validated(),
        );

        return (new InventoryItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update an inventory item.
     *
     * PUT /api/v1/inventory/{id}
     */
    public function update(UpdateInventoryItemRequest $request, string $id): JsonResponse
    {
        $item = $this->inventoryService->update(
            id: $id,
            tenantId: $request->attributes->get('tenant_id'),
            data: $request->validated(),
        );

        return (new InventoryItemResource($item))->response();
    }

    /**
     * Delete an inventory item.
     *
     * DELETE /api/v1/inventory/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->inventoryService->delete(
            id: $id,
            tenantId: $request->attributes->get('tenant_id'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully.',
        ]);
    }

    /**
     * Reserve stock for an order.
     *
     * POST /api/v1/inventory/{id}/reserve
     */
    public function reserve(ReserveStockRequest $request, string $id): JsonResponse
    {
        $item = $this->inventoryService->reserveStock(
            tenantId: $request->attributes->get('tenant_id'),
            itemId: $id,
            quantity: $request->validated('quantity'),
            orderId: $request->validated('order_id'),
        );

        return (new InventoryItemResource($item))->response();
    }

    /**
     * Release reserved stock.
     *
     * POST /api/v1/inventory/{id}/release
     */
    public function release(ReserveStockRequest $request, string $id): JsonResponse
    {
        $item = $this->inventoryService->releaseStock(
            tenantId: $request->attributes->get('tenant_id'),
            itemId: $id,
            quantity: $request->validated('quantity'),
            orderId: $request->validated('order_id'),
        );

        return (new InventoryItemResource($item))->response();
    }

    /**
     * Get items with low stock.
     *
     * GET /api/v1/inventory/low-stock
     */
    public function lowStock(Request $request): JsonResponse
    {
        $items = $this->inventoryService->getLowStockItems(
            tenantId: $request->attributes->get('tenant_id'),
        );

        return (new InventoryCollection($items))->response();
    }
}
