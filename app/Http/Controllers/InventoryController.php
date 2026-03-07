<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service B: Inventory Service Controller
 *
 * Exposes inventory management endpoints. Inventory records are primarily
 * created/updated/deleted via Service A events, but this controller also
 * allows direct inventory management (e.g. adjusting stock quantities).
 */
class InventoryController extends Controller
{
    /**
     * List all inventory records, optionally filtered by product name.
     * GET /api/inventories?product_name=Widget
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Inventory::with('product');

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->input('product_name') . '%');
        }

        return InventoryResource::collection($query->get());
    }

    /**
     * Show a single inventory record.
     * GET /api/inventories/{inventory}
     */
    public function show(Inventory $inventory): InventoryResource
    {
        $inventory->load('product');

        return new InventoryResource($inventory);
    }

    /**
     * Update an inventory record by its ID.
     * PUT /api/inventories/{inventory}
     *
     * @throws Throwable
     */
    public function update(Request $request, Inventory $inventory): JsonResponse
    {
        $validated = $request->validate([
            'quantity'           => 'sometimes|required|integer|min:0',
            'warehouse_location' => 'nullable|string|max:255',
            'status'             => 'sometimes|required|in:in_stock,low_stock,out_of_stock',
        ]);

        try {
            DB::transaction(function () use ($validated, $inventory) {
                $inventory->update($validated);
            });

            $inventory->load('product');

            Log::info('Service B: Inventory updated', ['inventory_id' => $inventory->id]);

            return response()->json([
                'message' => 'Inventory updated successfully.',
                'data'    => new InventoryResource($inventory),
            ]);
        } catch (Throwable $e) {
            Log::error('Service B: Failed to update inventory', [
                'inventory_id' => $inventory->id,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update inventory.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update inventory record(s) by product name.
     * PATCH /api/inventories/by-product-name
     *
     * @throws Throwable
     */
    public function updateByProductName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name'       => 'required|string|max:255',
            'quantity'           => 'sometimes|required|integer|min:0',
            'warehouse_location' => 'nullable|string|max:255',
            'status'             => 'sometimes|required|in:in_stock,low_stock,out_of_stock',
        ]);

        $productName = $validated['product_name'];
        $updates     = array_filter(
            $validated,
            fn ($key) => $key !== 'product_name',
            ARRAY_FILTER_USE_KEY
        );

        try {
            $count = DB::transaction(function () use ($productName, $updates) {
                return Inventory::where('product_name', $productName)->update($updates);
            });

            Log::info('Service B: Inventory updated by product name', [
                'product_name'    => $productName,
                'records_updated' => $count,
            ]);

            return response()->json([
                'message'         => 'Inventory records updated by product name.',
                'product_name'    => $productName,
                'records_updated' => $count,
            ]);
        } catch (Throwable $e) {
            Log::error('Service B: Failed to update inventory by product name', [
                'product_name' => $productName,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update inventory by product name.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an inventory record by its ID.
     * DELETE /api/inventories/{inventory}
     *
     * @throws Throwable
     */
    public function destroy(Inventory $inventory): JsonResponse
    {
        $inventoryId = $inventory->id;

        try {
            DB::transaction(function () use ($inventory) {
                $inventory->delete();
            });

            Log::info('Service B: Inventory record deleted', ['inventory_id' => $inventoryId]);

            return response()->json([
                'message' => 'Inventory record deleted successfully.',
            ]);
        } catch (Throwable $e) {
            Log::error('Service B: Failed to delete inventory record', [
                'inventory_id' => $inventoryId,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to delete inventory record.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
