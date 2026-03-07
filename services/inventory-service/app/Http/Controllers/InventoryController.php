<?php

namespace App\Http\Controllers;

use App\Application\Commands\CreateInventoryCommand;
use App\Application\Queries\GetInventoryQuery;
use App\Application\Services\InventoryService;
use App\Http\Requests\CreateInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Http\Resources\InventoryCollection;
use App\Http\Resources\InventoryResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $service) {}

    /**
     * GET /v1/inventories
     * List inventory with optional filtering, searching, sorting and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $query = new GetInventoryQuery(
            tenantId:      $tenantId,
            search:        $request->string('search')->value() ?: null,
            filters:       (array) $request->input('filters', []),
            sortBy:        $request->input('sort_by', 'created_at'),
            sortDirection: $request->input('sort_direction', 'desc'),
            perPage:       $request->has('per_page') ? (int) $request->input('per_page') : null,
            page:          (int) $request->input('page', 1),
        );

        $result = $this->service->listInventory($query);

        return (new InventoryCollection($result))->response()->setStatusCode(200);
    }

    /**
     * GET /v1/inventories/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        try {
            $item = $this->service->getInventory($id, $tenantId);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Not Found', 'message' => "Inventory item [{$id}] not found."], 404);
        } catch (\DomainException $e) {
            return response()->json(['error' => 'Forbidden', 'message' => $e->getMessage()], 403);
        }

        return (new InventoryResource($item))->response()->setStatusCode(200);
    }

    /**
     * POST /v1/inventories
     */
    public function store(CreateInventoryRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $command = new CreateInventoryCommand(
            tenantId:      $tenantId,
            sku:           $request->input('sku'),
            name:          $request->input('name'),
            quantity:      (int) $request->input('quantity'),
            unitCost:      (float) $request->input('unit_cost'),
            unitPrice:     (float) $request->input('unit_price'),
            description:   $request->input('description'),
            category:      $request->input('category'),
            location:      $request->input('location'),
            minStockLevel: (int) $request->input('min_stock_level', 0),
            maxStockLevel: (int) $request->input('max_stock_level', 9999),
            metadata:      (array) $request->input('metadata', []),
        );

        try {
            $item = $this->service->createInventory($command);
        } catch (\DomainException $e) {
            return response()->json(['error' => 'Conflict', 'message' => $e->getMessage()], 409);
        }

        return (new InventoryResource($item))->response()->setStatusCode(201);
    }

    /**
     * PUT /v1/inventories/{id}
     */
    public function update(UpdateInventoryRequest $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        try {
            $item = $this->service->updateInventory($id, $tenantId, $request->validated());
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Not Found', 'message' => "Inventory item [{$id}] not found."], 404);
        } catch (\DomainException $e) {
            return response()->json(['error' => 'Unprocessable', 'message' => $e->getMessage()], 422);
        }

        return (new InventoryResource($item))->response()->setStatusCode(200);
    }

    /**
     * DELETE /v1/inventories/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        try {
            $this->service->deleteInventory($id, $tenantId);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Not Found', 'message' => "Inventory item [{$id}] not found."], 404);
        } catch (\DomainException $e) {
            return response()->json(['error' => 'Forbidden', 'message' => $e->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    /**
     * POST /v1/inventories/{id}/adjust-stock
     * Body: { "quantity": 10, "operation": "increment" | "decrement" | "set" }
     */
    public function adjustStock(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity'  => 'required|integer|min:0',
            'operation' => 'nullable|in:set,increment,decrement',
        ]);

        $tenantId  = $request->attributes->get('tenant_id');
        $quantity  = (int) $request->input('quantity');
        $operation = $request->input('operation', 'increment');

        try {
            $item = $this->service->adjustStock($id, $tenantId, $quantity, $operation);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Not Found', 'message' => "Inventory item [{$id}] not found."], 404);
        } catch (\DomainException $e) {
            return response()->json(['error' => 'Forbidden', 'message' => $e->getMessage()], 403);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }

        return (new InventoryResource($item))->response()->setStatusCode(200);
    }

    /**
     * GET /v1/inventories/reports/low-stock
     */
    public function lowStock(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $items    = $this->service->getLowStockReport($tenantId);

        return (new InventoryCollection($items))->response()->setStatusCode(200);
    }
}
