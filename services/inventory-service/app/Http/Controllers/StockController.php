<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Inventory\Commands\AdjustStockCommand;
use App\Application\Inventory\Commands\ReserveStockCommand;
use App\Application\Inventory\Commands\ReleaseStockCommand;
use App\Application\Inventory\Queries\GetStockMovementsQuery;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\ReserveStockRequest;
use App\Http\Resources\StockMovementResource;
use App\Services\InventoryService;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StockController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {}

    public function adjust(AdjustStockRequest $request, string $productId): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $movement = $this->inventoryService->adjustStock(new AdjustStockCommand(
            productId: $productId,
            tenantId: $tenantId,
            newQuantity: (int) $request->validated()['quantity'],
            reason: $request->validated()['reason'],
            performedBy: $request->get('_auth_user_id', 'system'),
        ));
        return response()->json(['data' => (new StockMovementResource($movement))->toArray($request)]);
    }

    public function reserve(ReserveStockRequest $request, string $productId): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $reserved = $this->inventoryService->reserveStock(new ReserveStockCommand(
            productId: $productId,
            tenantId: $tenantId,
            quantity: (int) $request->validated()['quantity'],
            orderId: $request->validated()['order_id'],
        ));
        return response()->json(['success' => $reserved, 'message' => $reserved ? 'Stock reserved.' : 'Insufficient stock.']);
    }

    public function release(Request $request, string $productId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1', 'order_id' => 'required|string']);
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $this->inventoryService->releaseStock(new ReleaseStockCommand(
            productId: $productId,
            tenantId: $tenantId,
            quantity: (int) $request->input('quantity'),
            orderId: $request->input('order_id'),
        ));
        return response()->json(['message' => 'Stock released.']);
    }

    public function movements(Request $request, string $productId): JsonResponse
    {
        $movements = $this->movementRepository->findByProduct($productId, array_filter([
            'type' => $request->input('type'),
            'from' => $request->input('from'),
            'to'   => $request->input('to'),
        ]));
        return response()->json(['data' => array_map(fn ($m) => (new StockMovementResource($m->toArray()))->toArray($request), $movements)]);
    }
}
