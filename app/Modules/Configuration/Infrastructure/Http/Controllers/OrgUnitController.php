<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Configuration\Application\Contracts\CreateOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\DeleteOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\MoveOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\OrgUnitTreeServiceInterface;
use Modules\Configuration\Application\Contracts\UpdateOrgUnitServiceInterface;
use Modules\Configuration\Application\DTOs\CreateOrgUnitData;
use Modules\Configuration\Application\DTOs\MoveOrgUnitData;
use Modules\Configuration\Application\DTOs\UpdateOrgUnitData;
use Modules\Configuration\Domain\Repositories\OrgUnitRepositoryInterface;
use Modules\Configuration\Infrastructure\Http\Requests\CreateOrgUnitRequest;
use Modules\Configuration\Infrastructure\Http\Requests\MoveOrgUnitRequest;
use Modules\Configuration\Infrastructure\Http\Requests\UpdateOrgUnitRequest;
use Modules\Configuration\Infrastructure\Http\Resources\OrgUnitResource;

class OrgUnitController extends Controller
{
    public function __construct(
        private readonly CreateOrgUnitServiceInterface $createService,
        private readonly UpdateOrgUnitServiceInterface $updateService,
        private readonly DeleteOrgUnitServiceInterface $deleteService,
        private readonly MoveOrgUnitServiceInterface $moveService,
        private readonly OrgUnitTreeServiceInterface $treeService,
        private readonly OrgUnitRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $units = $this->repository->findByTenant(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => OrgUnitResource::collection($units->items()),
            'meta' => [
                'current_page' => $units->currentPage(),
                'last_page'    => $units->lastPage(),
                'per_page'     => $units->perPage(),
                'total'        => $units->total(),
            ],
        ]);
    }

    public function store(CreateOrgUnitRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateOrgUnitData::fromArray([
            'tenantId'    => $validated['tenant_id'],
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'type'        => $validated['type'],
            'parentId'    => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'isActive'    => $validated['is_active'] ?? true,
            'metadata'    => $validated['metadata'] ?? null,
            'createdBy'   => $request->user()?->id,
        ]);

        $orgUnit = $this->createService->execute($data);

        return response()->json(new OrgUnitResource($orgUnit), 201);
    }

    public function show(int $id): JsonResponse
    {
        $orgUnit = $this->repository->findById($id);

        if ($orgUnit === null) {
            return response()->json(['message' => 'OrgUnit not found.'], 404);
        }

        return response()->json(new OrgUnitResource($orgUnit));
    }

    public function update(UpdateOrgUnitRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $data = UpdateOrgUnitData::fromArray([
            'name'        => $validated['name'] ?? null,
            'code'        => $validated['code'] ?? null,
            'type'        => $validated['type'] ?? null,
            'description' => $validated['description'] ?? null,
            'isActive'    => $validated['is_active'] ?? null,
            'metadata'    => $validated['metadata'] ?? null,
            'updatedBy'   => $request->user()?->id,
        ]);

        $orgUnit = $this->updateService->execute($id, $data);

        return response()->json(new OrgUnitResource($orgUnit));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }

    public function tree(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $tree = $this->treeService->getTree($tenantId);

        return response()->json(['data' => $tree]);
    }

    public function move(MoveOrgUnitRequest $request, int $id): JsonResponse
    {
        $parentId = $request->validated()['parent_id'] ?? null;
        $orgUnit = $this->moveService->execute($id, $parentId !== null ? (int) $parentId : null);

        return response()->json(new OrgUnitResource($orgUnit));
    }
}
