<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Requests\CreateInventoryRequest;
use App\Modules\Inventory\Requests\UpdateInventoryRequest;
use App\Modules\Inventory\Requests\AdjustQuantityRequest;
use App\Modules\Inventory\Resources\InventoryResource;
use App\Modules\Inventory\Resources\InventoryCollection;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    public function index(Request $request): InventoryCollection
    {
        $filters = $request->only([
            'product_id',
            'warehouse_location',
            'low_stock',
            'in_stock',
            'search',
            'sort_by',
            'sort_direction',
        ]);

        $perPage   = min((int) $request->input('per_page', 15), 100);
        $inventory = $this->inventoryService->listInventory($filters, $perPage);

        return new InventoryCollection($inventory);
    }

    public function show(int $id): JsonResponse
    {
        $inventory = $this->inventoryService->getInventory($id);
        return response()->json(new InventoryResource($inventory));
    }

    public function showByProduct(int $productId): JsonResponse
    {
        $inventory = $this->inventoryService->getInventoryByProductId($productId);
        if (!$inventory) {
            return response()->json(['error' => 'Inventory not found for product'], 404);
        }
        return response()->json(new InventoryResource($inventory));
    }

    public function store(CreateInventoryRequest $request): JsonResponse
    {
        $dto       = InventoryDTO::fromRequest($request->validated());
        $inventory = $this->inventoryService->createInventory($dto);
        return response()->json(new InventoryResource($inventory), 201);
    }

    public function update(UpdateInventoryRequest $request, int $id): JsonResponse
    {
        $dto       = InventoryDTO::fromRequest($request->validated());
        $inventory = $this->inventoryService->updateInventory($id, $dto);
        return response()->json(new InventoryResource($inventory));
    }

    public function adjust(AdjustQuantityRequest $request, int $productId): JsonResponse
    {
        $inventory = $this->inventoryService->adjustQuantity(
            productId: $productId,
            delta:     $request->input('delta'),
            reason:    $request->input('reason', 'manual')
        );
        return response()->json(new InventoryResource($inventory));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->inventoryService->deleteInventory($id);
        return response()->json(null, 204);
    }
}
