<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Warehouse\Application\Contracts\CreateLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\DeleteLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\GetLocationTreeServiceInterface;
use Modules\Warehouse\Application\Contracts\MoveLocationServiceInterface;
use Modules\Warehouse\Application\Contracts\UpdateLocationServiceInterface;
use Modules\Warehouse\Application\DTOs\CreateLocationData;
use Modules\Warehouse\Application\DTOs\MoveLocationData;
use Modules\Warehouse\Application\DTOs\UpdateLocationData;
use Modules\Warehouse\Domain\Exceptions\LocationNotFoundException;
use Modules\Warehouse\Domain\Repositories\WarehouseLocationRepositoryInterface;
use Modules\Warehouse\Infrastructure\Http\Requests\CreateLocationRequest;
use Modules\Warehouse\Infrastructure\Http\Requests\MoveLocationRequest;
use Modules\Warehouse\Infrastructure\Http\Requests\UpdateLocationRequest;
use Modules\Warehouse\Infrastructure\Http\Resources\WarehouseLocationResource;

class WarehouseLocationController extends Controller
{
    public function __construct(
        private readonly CreateLocationServiceInterface $createService,
        private readonly UpdateLocationServiceInterface $updateService,
        private readonly DeleteLocationServiceInterface $deleteService,
        private readonly MoveLocationServiceInterface $moveService,
        private readonly GetLocationTreeServiceInterface $treeService,
        private readonly WarehouseLocationRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $warehouseId = (int) $request->get('warehouse_id', 0);
        $locations = $this->repository->findByWarehouse(
            $warehouseId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => WarehouseLocationResource::collection($locations->items()),
            'meta' => [
                'current_page' => $locations->currentPage(),
                'last_page'    => $locations->lastPage(),
                'per_page'     => $locations->perPage(),
                'total'        => $locations->total(),
            ],
        ]);
    }

    public function store(CreateLocationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateLocationData::fromArray([
            'tenantId'    => $validated['tenant_id'],
            'warehouseId' => $validated['warehouse_id'],
            'parentId'    => $validated['parent_id'] ?? null,
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'type'        => $validated['type'] ?? 'bin',
            'barcode'     => $validated['barcode'] ?? null,
            'capacity'    => $validated['capacity'] ?? null,
            'isActive'    => $validated['is_active'] ?? true,
            'createdBy'   => $request->user()?->id,
        ]);

        $location = $this->createService->execute($data);

        return response()->json(new WarehouseLocationResource($location), 201);
    }

    public function show(int $id): JsonResponse
    {
        $location = $this->repository->findById($id);
        if ($location === null) {
            return response()->json(['message' => 'Location not found.'], 404);
        }

        return response()->json(new WarehouseLocationResource($location));
    }

    public function update(UpdateLocationRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $data = UpdateLocationData::fromArray([
                'name'      => $validated['name'] ?? null,
                'code'      => $validated['code'] ?? null,
                'type'      => $validated['type'] ?? null,
                'barcode'   => $validated['barcode'] ?? null,
                'capacity'  => $validated['capacity'] ?? null,
                'isActive'  => $validated['is_active'] ?? null,
                'updatedBy' => $request->user()?->id,
            ]);

            $location = $this->updateService->execute($id, $data);

            return response()->json(new WarehouseLocationResource($location));
        } catch (LocationNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteService->execute($id);

            return response()->json(null, 204);
        } catch (LocationNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function tree(int $warehouseId): JsonResponse
    {
        $tree = $this->treeService->execute($warehouseId);

        return response()->json(['data' => $tree]);
    }

    public function move(MoveLocationRequest $request, int $id): JsonResponse
    {
        try {
            $data = MoveLocationData::fromArray([
                'locationId'  => $id,
                'newParentId' => $request->validated()['parent_id'] ?? null,
                'updatedBy'   => $request->user()?->id,
            ]);

            $location = $this->moveService->execute($data);

            return response()->json(new WarehouseLocationResource($location));
        } catch (LocationNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
