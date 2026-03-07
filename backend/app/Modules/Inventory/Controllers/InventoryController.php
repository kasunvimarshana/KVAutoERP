<?php

namespace App\Modules\Inventory\Controllers;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Requests\CreateInventoryRequest;
use App\Modules\Inventory\Requests\UpdateInventoryRequest;
use App\Modules\Inventory\Resources\InventoryResource;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = app('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $filters  = $request->only(['status', 'warehouse_location', 'low_stock']);

        return InventoryResource::collection(
            $this->inventoryService->list($tenantId, $perPage, $filters)
        );
    }

    public function show(string $id): InventoryResource
    {
        $tenantId = app('tenant_id');
        return new InventoryResource($this->inventoryService->findById($id, $tenantId));
    }

    public function store(CreateInventoryRequest $request): JsonResponse
    {
        $tenantId = app('tenant_id');
        $data     = $request->validated();
        $data['tenant_id'] = $tenantId;

        $inventory = $this->inventoryService->create(InventoryDTO::fromRequest($data));

        return (new InventoryResource($inventory))->response()->setStatusCode(201);
    }

    public function update(UpdateInventoryRequest $request, string $id): InventoryResource
    {
        $tenantId  = app('tenant_id');
        $inventory = $this->inventoryService->update($id, $tenantId, $request->validated());

        return new InventoryResource($inventory);
    }

    public function adjustStock(Request $request, string $id): InventoryResource
    {
        $request->validate([
            'delta'  => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $tenantId  = app('tenant_id');
        $inventory = $this->inventoryService->adjustStock(
            $id,
            $tenantId,
            $request->integer('delta'),
            $request->input('reason', '')
        );

        return new InventoryResource($inventory);
    }

    public function reserveStock(Request $request, string $id): JsonResponse
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        $tenantId = app('tenant_id');
        $reserved = $this->inventoryService->reserveStock($id, $tenantId, $request->integer('quantity'));

        return response()->json([
            'success' => $reserved,
            'message' => $reserved ? 'Stock reserved successfully' : 'Insufficient stock',
        ], $reserved ? 200 : 409);
    }

    public function releaseStock(Request $request, string $id): JsonResponse
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        $tenantId = app('tenant_id');
        $this->inventoryService->releaseStock($id, $tenantId, $request->integer('quantity'));

        return response()->json(['message' => 'Stock released successfully']);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = app('tenant_id');
        $this->inventoryService->delete($id, $tenantId);

        return response()->json(['message' => 'Inventory record deleted']);
    }
}
