<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Application\Contracts\CreateRoleServiceInterface;
use Modules\Auth\Application\Contracts\DeleteRoleServiceInterface;
use Modules\Auth\Application\Contracts\SyncRolePermissionsServiceInterface;
use Modules\Auth\Application\Contracts\UpdateRoleServiceInterface;
use Modules\Auth\Application\DTOs\CreateRoleData;
use Modules\Auth\Application\DTOs\UpdateRoleData;
use Modules\Auth\Domain\Repositories\RoleRepositoryInterface;
use Modules\Auth\Infrastructure\Http\Requests\CreateRoleRequest;
use Modules\Auth\Infrastructure\Http\Requests\SyncPermissionsRequest;
use Modules\Auth\Infrastructure\Http\Requests\UpdateRoleRequest;
use Modules\Auth\Infrastructure\Http\Resources\RoleResource;

class RoleController extends Controller
{
    public function __construct(
        private readonly CreateRoleServiceInterface $createService,
        private readonly UpdateRoleServiceInterface $updateService,
        private readonly DeleteRoleServiceInterface $deleteService,
        private readonly SyncRolePermissionsServiceInterface $syncService,
        private readonly RoleRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $roles = $this->repository->findByTenant(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => RoleResource::collection($roles->items()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page'    => $roles->lastPage(),
                'per_page'     => $roles->perPage(),
                'total'        => $roles->total(),
            ],
        ]);
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateRoleData::fromArray([
            'tenantId'    => $validated['tenant_id'],
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'isSystem'    => $validated['is_system'] ?? false,
        ]);

        $role = $this->createService->execute($data);

        return response()->json(new RoleResource($role), 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->repository->findById($id);

        if ($role === null) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        return response()->json(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $data = UpdateRoleData::fromArray($request->validated());
        $role = $this->updateService->execute($id, $data);

        return response()->json(new RoleResource($role));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }

    public function syncPermissions(SyncPermissionsRequest $request, int $id): JsonResponse
    {
        $this->syncService->execute($id, $request->validated()['permission_ids']);

        return response()->json(['message' => 'Permissions synced successfully.']);
    }
}
