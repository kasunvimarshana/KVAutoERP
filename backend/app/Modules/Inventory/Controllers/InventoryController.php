<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Requests\AdjustInventoryRequest;
use App\Modules\Inventory\Requests\CreateInventoryRequest;
use App\Modules\Inventory\Requests\UpdateInventoryRequest;
use App\Modules\Inventory\Resources\InventoryResource;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['tenant_id', 'product_id', 'low_stock', 'sort_by', 'sort_dir']);
        $perPage = $request->input('per_page', 15);
        $inventories = $this->inventoryService->list($filters, $perPage);
        return InventoryResource::collection($inventories);
    }

    public function show(int $id): InventoryResource
    {
        $inventory = $this->inventoryService->get($id);
        return new InventoryResource($inventory);
    }

    public function store(CreateInventoryRequest $request): JsonResponse
    {
        $dto = InventoryDTO::fromArray($request->validated());
        $inventory = $this->inventoryService->create($dto);
        return (new InventoryResource($inventory))->response()->setStatusCode(201);
    }

    public function update(UpdateInventoryRequest $request, int $id): InventoryResource
    {
        $dto = InventoryDTO::fromArray($request->validated());
        $inventory = $this->inventoryService->update($id, $dto);
        return new InventoryResource($inventory);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->inventoryService->delete($id);
        return response()->json(['message' => 'Inventory deleted successfully']);
    }

    public function adjust(AdjustInventoryRequest $request, int $id): InventoryResource
    {
        $adjustment = $request->input('adjustment');
        $inventory = $this->inventoryService->adjustQuantity($id, $adjustment);
        return new InventoryResource($inventory);
    }
}
