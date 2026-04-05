<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;

class CycleCountController extends \Illuminate\Routing\Controller
{
    public function __construct(
        private readonly CycleCountRepositoryInterface $repo,
        private readonly CreateCycleCountServiceInterface $createService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId    = (int) $request->header('X-Tenant-ID', 0);
        $cycleCounts = $this->repo->allByTenant($tenantId);

        return response()->json(['data' => $cycleCounts]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId   = (int) $request->header('X-Tenant-ID', 0);
        $cycleCount = $this->repo->findById($id, $tenantId);

        if ($cycleCount === null) {
            return response()->json(['message' => 'Cycle count not found.'], 404);
        }

        $lines = $this->repo->findLines($id, $tenantId);

        return response()->json(['data' => $cycleCount, 'lines' => $lines]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId   = (int) $request->header('X-Tenant-ID', 0);
        $locationId = (int) $request->input('location_id', 0);
        $productIds = $request->input('product_ids', []);
        $createdBy  = $request->input('created_by') !== null ? (int) $request->input('created_by') : null;

        if ($locationId === 0) {
            return response()->json(['message' => 'location_id is required.'], 422);
        }

        try {
            $cycleCount = $this->createService->create($tenantId, $locationId, $productIds, $createdBy);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $cycleCount], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $tenantId   = (int) $request->header('X-Tenant-ID', 0);
        $cycleCount = $this->repo->findById($id, $tenantId);

        if ($cycleCount === null) {
            return response()->json(['message' => 'Cycle count not found.'], 404);
        }

        $data = $request->only(['status', 'started_at', 'completed_at']);

        // Update counted quantities for lines if provided
        foreach ($request->input('lines', []) as $lineData) {
            if (!empty($lineData['id'])) {
                $this->repo->updateLine(
                    (int) $lineData['id'],
                    ['counted_quantity' => $lineData['counted_quantity'] ?? null],
                );
            }
        }

        try {
            $updated = $this->repo->update($id, $data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $updated]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $tenantId   = (int) $request->header('X-Tenant-ID', 0);
        $cycleCount = $this->repo->findById($id, $tenantId);

        if ($cycleCount === null) {
            return response()->json(['message' => 'Cycle count not found.'], 404);
        }

        if (in_array($cycleCount->status, ['in_progress', 'completed'], true)) {
            return response()->json(
                ['message' => 'Cannot delete an in-progress or completed cycle count.'],
                409,
            );
        }

        $this->repo->delete($id, $tenantId);

        return response()->json(null, 204);
    }
}
