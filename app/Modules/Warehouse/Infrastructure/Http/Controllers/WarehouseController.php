<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Warehouse\Application\Contracts\CreateWarehouseServiceInterface;
use Modules\Warehouse\Application\Contracts\DeleteWarehouseServiceInterface;
use Modules\Warehouse\Application\Contracts\ListWarehousesServiceInterface;
use Modules\Warehouse\Application\Contracts\UpdateWarehouseServiceInterface;
use Modules\Warehouse\Application\DTOs\CreateWarehouseData;
use Modules\Warehouse\Application\DTOs\UpdateWarehouseData;
use Modules\Warehouse\Domain\Exceptions\WarehouseNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Modules\Warehouse\Infrastructure\Http\Requests\CreateWarehouseRequest;
use Modules\Warehouse\Infrastructure\Http\Requests\UpdateWarehouseRequest;
use Modules\Warehouse\Infrastructure\Http\Resources\WarehouseResource;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly CreateWarehouseServiceInterface $createService,
        private readonly UpdateWarehouseServiceInterface $updateService,
        private readonly DeleteWarehouseServiceInterface $deleteService,
        private readonly ListWarehousesServiceInterface $listService,
        private readonly WarehouseRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $warehouses = $this->listService->execute(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => WarehouseResource::collection($warehouses->items()),
            'meta' => [
                'current_page' => $warehouses->currentPage(),
                'last_page'    => $warehouses->lastPage(),
                'per_page'     => $warehouses->perPage(),
                'total'        => $warehouses->total(),
            ],
        ]);
    }

    public function store(CreateWarehouseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateWarehouseData::fromArray([
            'tenantId'       => $validated['tenant_id'],
            'name'           => $validated['name'],
            'code'           => $validated['code'],
            'type'           => $validated['type'] ?? 'standard',
            'address'        => $validated['address'] ?? null,
            'isActive'       => $validated['is_active'] ?? true,
            'managerUserId'  => $validated['manager_user_id'] ?? null,
            'createdBy'      => $request->user()?->id,
        ]);

        $warehouse = $this->createService->execute($data);

        return response()->json(new WarehouseResource($warehouse), 201);
    }

    public function show(int $id): JsonResponse
    {
        $warehouse = $this->repository->findById($id);
        if ($warehouse === null) {
            return response()->json(['message' => 'Warehouse not found.'], 404);
        }

        return response()->json(new WarehouseResource($warehouse));
    }

    public function update(UpdateWarehouseRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $data = UpdateWarehouseData::fromArray([
                'name'          => $validated['name'] ?? null,
                'code'          => $validated['code'] ?? null,
                'type'          => $validated['type'] ?? null,
                'address'       => $validated['address'] ?? null,
                'isActive'      => $validated['is_active'] ?? null,
                'managerUserId' => $validated['manager_user_id'] ?? null,
                'updatedBy'     => $request->user()?->id,
            ]);

            $warehouse = $this->updateService->execute($id, $data);

            return response()->json(new WarehouseResource($warehouse));
        } catch (WarehouseNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteService->execute($id);

            return response()->json(null, 204);
        } catch (WarehouseNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
