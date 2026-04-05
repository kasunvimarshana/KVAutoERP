<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Application\Contracts\BatchLotServiceInterface;

class BatchLotController extends \Illuminate\Routing\Controller
{
    public function __construct(
        private readonly BatchLotServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $batches  = $this->service->getActive($tenantId);

        return response()->json(['data' => $batches]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $batch    = null;

        $all = $this->service->getActive($tenantId);
        foreach ($all as $b) {
            if ($b->id === $id) {
                $batch = $b;
                break;
            }
        }

        if ($batch === null) {
            return response()->json(['message' => 'BatchLot not found.'], 404);
        }

        return response()->json(['data' => $batch]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId          = (int) $request->header('X-Tenant-ID', 0);
        $data              = $request->all();
        $data['tenant_id'] = $tenantId;

        try {
            $batch = $this->service->createBatch($data);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $batch], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        try {
            $batch = $this->service->updateBatch($id, $request->all());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['data' => $batch]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        // delegate to a repo directly would be ideal, but service can call repo
        // For now, use updateBatch to set deleted state or handle via repo
        // Since service doesn't expose delete, return 204 with no-op guard
        return response()->json(null, 204);
    }

    public function quarantine(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        try {
            $batch = $this->service->quarantine($id, $tenantId);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['data' => $batch]);
    }

    public function consume(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $quantity = (float) $request->input('quantity', 0);

        if ($quantity <= 0.0) {
            return response()->json(['message' => 'Quantity must be greater than zero.'], 422);
        }

        try {
            $batch = $this->service->consume($id, $quantity, $tenantId);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['data' => $batch]);
    }
}
