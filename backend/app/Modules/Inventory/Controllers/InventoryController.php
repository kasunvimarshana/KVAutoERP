<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->inventoryService->index($request->query()));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'        => 'required|exists:products,id',
            'warehouse'         => 'required|string|max:255',
            'quantity'          => 'required|integer|min:0',
            'reserved_quantity' => 'integer|min:0',
            'min_quantity'      => 'integer|min:0',
            'max_quantity'      => 'nullable|integer|min:0',
            'location'          => 'nullable|string|max:255',
        ]);

        return response()->json($this->inventoryService->store($request->all()), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->inventoryService->show($id)->load('product'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'warehouse'    => 'sometimes|string|max:255',
            'quantity'     => 'sometimes|integer|min:0',
            'min_quantity' => 'sometimes|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'location'     => 'nullable|string|max:255',
        ]);

        return response()->json($this->inventoryService->update($id, $request->all()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->inventoryService->destroy($id);

        return response()->json(['message' => 'Inventory record deleted.']);
    }

    public function adjustQuantity(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'delta'  => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        return response()->json(
            $this->inventoryService->adjustQuantity($id, $request->integer('delta'), $request->string('reason', ''))
        );
    }

    public function reserve(Request $request, int $id): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        return response()->json($this->inventoryService->reserveQuantity($id, $request->integer('quantity')));
    }

    public function release(Request $request, int $id): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        return response()->json($this->inventoryService->releaseReservation($id, $request->integer('quantity')));
    }
}
