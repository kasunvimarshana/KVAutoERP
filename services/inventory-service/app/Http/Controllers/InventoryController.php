<?php
namespace App\Http\Controllers;

use App\Services\InventoryService;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Http\Requests\AdjustInventoryRequest;
use App\Http\Requests\StockOperationRequest;
use App\Http\Resources\InventoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);
        $data     = $this->inventoryService->list($tenantId, $request->all());
        $isPag    = $data instanceof \Illuminate\Pagination\AbstractPaginator;
        return response()->json([
            'success' => true,
            'data'    => InventoryResource::collection($isPag ? $data->getCollection() : $data),
            'meta'    => $isPag ? ['total' => $data->total(), 'current_page' => $data->currentPage(), 'per_page' => $data->perPage(), 'last_page' => $data->lastPage()] : null,
        ]);
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        try {
            $inv = $this->inventoryService->create($this->tenantId($request), $request->validated());
            return response()->json(new InventoryResource($inv), 201);
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            return response()->json(new InventoryResource($this->inventoryService->get($id, $this->tenantId($request))));
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    public function update(UpdateInventoryRequest $request, string $id): JsonResponse
    {
        try {
            return response()->json(new InventoryResource($this->inventoryService->update($id, $this->tenantId($request), $request->validated())));
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->inventoryService->delete($id, $this->tenantId($request));
            return response()->json(['success' => true, 'message' => 'Inventory record deleted.']);
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    public function adjust(AdjustInventoryRequest $request, string $id): JsonResponse
    {
        try {
            $inv = $this->inventoryService->adjustStock(
                $id, $this->tenantId($request),
                $request->validated('quantity'),
                $request->validated('type'),
                $request->validated('notes', '')
            );
            return response()->json(new InventoryResource($inv));
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    /** Saga Step 1: Reserve stock for an order. */
    public function reserve(StockOperationRequest $request): JsonResponse
    {
        try {
            $inv = $this->inventoryService->reserveStock(
                $this->tenantId($request),
                $request->validated('product_id'),
                $request->validated('quantity'),
                $request->validated('order_id')
            );
            return response()->json(['success' => true, 'data' => new InventoryResource($inv)]);
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    /** Saga Compensating Action: Release reserved stock (rollback). */
    public function release(StockOperationRequest $request): JsonResponse
    {
        try {
            $inv = $this->inventoryService->releaseStock(
                $this->tenantId($request),
                $request->validated('product_id'),
                $request->validated('quantity'),
                $request->validated('order_id')
            );
            return response()->json(['success' => true, 'data' => new InventoryResource($inv), 'message' => 'Stock released (rollback).']);
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    /** Saga Step 3: Confirm stock deduction after payment. */
    public function confirm(StockOperationRequest $request): JsonResponse
    {
        try {
            $inv = $this->inventoryService->confirmDeduction(
                $this->tenantId($request),
                $request->validated('product_id'),
                $request->validated('quantity'),
                $request->validated('order_id')
            );
            return response()->json(['success' => true, 'data' => new InventoryResource($inv)]);
        } catch (\RuntimeException $e) { return $this->error($e); }
    }

    private function tenantId(Request $request): string
    {
        return $request->attributes->get('tenant_id', $request->header('X-Tenant-ID', ''));
    }

    private function error(\RuntimeException $e): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
}
