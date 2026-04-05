<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Application\Contracts\ReconcileInventoryServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryAdjustmentRepositoryInterface;

class InventoryAdjustmentController extends \Illuminate\Routing\Controller
{
    public function __construct(
        private readonly InventoryAdjustmentRepositoryInterface $repo,
        private readonly ReconcileInventoryServiceInterface $reconcileService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId    = (int) $request->header('X-Tenant-ID', 0);
        $adjustments = $this->repo->allByTenant($tenantId);

        return response()->json(['data' => $adjustments]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId   = (int) $request->header('X-Tenant-ID', 0);
        $adjustment = $this->repo->findById($id, $tenantId);

        if ($adjustment === null) {
            return response()->json(['message' => 'Inventory adjustment not found.'], 404);
        }

        $lines = $this->repo->findLines($id, $tenantId);

        return response()->json(['data' => $adjustment, 'lines' => $lines]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId          = (int) $request->header('X-Tenant-ID', 0);
        $data              = $request->all();
        $data['tenant_id'] = $tenantId;

        if (empty($data['adjustment_number'])) {
            $data['adjustment_number'] = 'ADJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid('', true), -6));
        }

        if (empty($data['date'])) {
            $data['date'] = date('Y-m-d');
        }

        try {
            $adjustment = $this->repo->create($data);

            foreach ($request->input('lines', []) as $line) {
                $line['tenant_id']    = $tenantId;
                $line['adjustment_id'] = $adjustment->id;
                $this->repo->createLine($line);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $adjustment], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        $adjustment = $this->repo->findById($id, $tenantId);
        if ($adjustment === null) {
            return response()->json(['message' => 'Inventory adjustment not found.'], 404);
        }

        if ($adjustment->status === 'applied') {
            return response()->json(['message' => 'Applied adjustments cannot be modified.'], 409);
        }

        try {
            $updated = $this->repo->update($id, $request->all());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $updated]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        $adjustment = $this->repo->findById($id, $tenantId);
        if ($adjustment === null) {
            return response()->json(['message' => 'Inventory adjustment not found.'], 404);
        }

        if ($adjustment->status === 'applied') {
            return response()->json(['message' => 'Applied adjustments cannot be deleted.'], 409);
        }

        $this->repo->delete($id, $tenantId);

        return response()->json(null, 204);
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        $adjustment = $this->repo->findById($id, $tenantId);
        if ($adjustment === null) {
            return response()->json(['message' => 'Inventory adjustment not found.'], 404);
        }

        if ($adjustment->status !== 'draft') {
            return response()->json(['message' => 'Only draft adjustments can be approved.'], 409);
        }

        $approvedBy  = (int) $request->input('approved_by', 0) ?: null;
        $updated = $this->repo->update($id, [
            'status'      => 'approved',
            'approved_by' => $approvedBy,
        ]);

        return response()->json(['data' => $updated]);
    }

    public function apply(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        try {
            $adjustment = $this->reconcileService->reconcile($tenantId, $id);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['data' => $adjustment]);
    }
}
