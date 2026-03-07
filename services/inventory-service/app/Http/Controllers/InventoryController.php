<?php
namespace App\Http\Controllers;

use App\DTOs\InventoryDTO;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\CreateInventoryRequest;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $page     = (int) $request->query('page', 1);

        $filters = array_filter([
            'product_id'         => $request->query('product_id'),
            'warehouse_location' => $request->query('warehouse_location'),
            'low_stock'          => $request->boolean('low_stock') ?: null,
            'status'             => $request->query('status'),
        ]);

        $paginator = $this->inventoryService->listInventory($tenantId, $filters, $perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => collect($paginator->items())->map(
                fn ($inv) => InventoryDTO::fromModel($inv)->toArray()
            ),
            'message' => 'Inventory retrieved successfully.',
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
        $dto      = $this->inventoryService->getInventory($tenantId, $id);

        if ($dto === null) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Inventory record not found.', 'meta' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Inventory record retrieved successfully.',
            'meta'    => [],
        ]);
    }

    public function store(CreateInventoryRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $dto      = $this->inventoryService->createInventory($tenantId, $request->validated());

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Inventory record created successfully.',
            'meta'    => [],
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $validated = $request->validate([
            'warehouse_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'min_level'          => ['sometimes', 'integer', 'min:0'],
            'max_level'          => ['sometimes', 'integer', 'min:0'],
            'unit'               => ['sometimes', 'nullable', 'string', 'max:50'],
            'status'             => ['sometimes', 'in:active,inactive'],
            'notes'              => ['sometimes', 'nullable', 'string'],
        ]);

        $dto = $this->inventoryService->updateInventory($tenantId, $id, $validated);

        if ($dto === null) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Inventory record not found.', 'meta' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Inventory record updated successfully.',
            'meta'    => [],
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $deleted  = $this->inventoryService->deleteInventory($tenantId, $id);

        return response()->json([
            'success' => $deleted,
            'data'    => null,
            'message' => $deleted ? 'Inventory record deleted.' : 'Inventory record not found.',
            'meta'    => [],
        ], $deleted ? 200 : 404);
    }

    public function adjustStock(AdjustStockRequest $request, string $id): JsonResponse
    {
        $tenantId  = $request->attributes->get('tenant_id');
        $validated = $request->validated();

        $dto = $this->inventoryService->adjustStock(
            tenantId:      $tenantId,
            inventoryId:   $id,
            quantity:      $validated['quantity'],
            movementType:  $validated['movement_type'],
            notes:         $validated['notes'] ?? null,
            referenceType: $validated['reference_type'] ?? null,
            referenceId:   $validated['reference_id'] ?? null,
        );

        if ($dto === null) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Inventory record not found.', 'meta' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'Stock adjusted successfully.',
            'meta'    => [],
        ]);
    }

    public function getByProduct(Request $request, string $productId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $items    = $this->inventoryService->getByProduct($tenantId, $productId);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'message' => 'Inventory for product retrieved.',
            'meta'    => ['count' => count($items)],
        ]);
    }

    public function listWithProductDetails(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $page     = (int) $request->query('page', 1);

        $result = $this->inventoryService->listWithProductDetails($tenantId, $perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => $result['data'],
            'message' => 'Inventory with product details retrieved.',
            'meta'    => $result['meta'],
        ]);
    }

    public function filterByProductName(Request $request): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'min:1']]);

        $tenantId    = $request->attributes->get('tenant_id');
        $productName = $request->query('name');
        $perPage     = (int) $request->query('per_page', 15);
        $page        = (int) $request->query('page', 1);

        $result = $this->inventoryService->filterByProductName($tenantId, $productName, $perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => $result['data'],
            'message' => 'Inventory filtered by product name.',
            'meta'    => $result['meta'],
        ]);
    }
}
