<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Application\Contracts\InventoryManagerServiceInterface;
use Modules\Inventory\Application\Contracts\StockMovementServiceInterface;

class StockMovementController extends \Illuminate\Routing\Controller
{
    public function __construct(
        private readonly StockMovementServiceInterface $movementService,
        private readonly InventoryManagerServiceInterface $inventoryManager,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        if ($productId = $request->query('product_id')) {
            $movements = $this->movementService->getByProduct((int) $productId, $tenantId);
        } elseif ($locationId = $request->query('location_id')) {
            $movements = $this->movementService->getByLocation((int) $locationId, $tenantId);
        } elseif ($batchNumber = $request->query('batch_number')) {
            $movements = $this->movementService->getByBatch((string) $batchNumber, $tenantId);
        } else {
            // Return by tenant — delegate to the repository via service
            $movements = $this->movementService->getByProduct(0, $tenantId);
        }

        return response()->json(['data' => $movements]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId  = (int) $request->header('X-Tenant-ID', 0);
        $movements = $this->movementService->getByProduct(0, $tenantId);
        $movement  = collect($movements)->firstWhere('id', $id);

        if ($movement === null) {
            return response()->json(['message' => 'Movement not found.'], 404);
        }

        return response()->json(['data' => $movement]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $action   = $request->input('action');

        $data              = $request->all();
        $data['tenant_id'] = $tenantId;

        try {
            $movement = match ($action) {
                'receive'  => $this->inventoryManager->receive($data),
                'issue'    => $this->inventoryManager->issue($data),
                'transfer' => $this->inventoryManager->transfer($data),
                default    => throw new \InvalidArgumentException("Invalid action: {$action}. Must be receive|issue|transfer."),
            };
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['data' => $movement], 201);
    }
}
