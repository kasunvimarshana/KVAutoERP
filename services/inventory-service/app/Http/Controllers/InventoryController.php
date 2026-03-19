<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use Shared\Core\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends BaseController
{
    /**
     * @var InventoryService
     */
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Records a stock movement (Ledger-driven)
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'bin_location_id' => 'nullable|integer',
            'lot_id' => 'nullable|integer',
            'serial_id' => 'nullable|integer',
            'quantity' => 'required|numeric',
            'uom_id' => 'required|integer',
            'transaction_type' => 'required|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'reference_number' => 'nullable|string',
        ]);

        try {
            $ledger = $this->inventoryService->recordTransaction($validated);
            return $this->success($ledger, 'Stock transaction recorded successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Reserve stock for an order
     */
    public function reserve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'quantity' => 'required|numeric',
            'reference_type' => 'required|string',
            'reference_id' => 'required|integer',
            'reference_number' => 'required|string',
            'lot_id' => 'nullable|integer',
        ]);

        try {
            $reservation = $this->inventoryService->reserveStock($validated);
            return $this->success([
                'reservation_id' => $reservation->id,
                'expiry_date' => $reservation->expiry_date
            ], 'Stock reserved successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Release a reservation
     */
    public function release($id): JsonResponse
    {
        $released = $this->inventoryService->releaseReservation($id);
        
        if ($released) {
            return $this->success(null, 'Stock reservation released.');
        }

        return $this->error('Failed to release reservation.');
    }
}
