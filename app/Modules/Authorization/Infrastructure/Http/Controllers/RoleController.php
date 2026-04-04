<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authorization\Application\Contracts\AssignPermissionServiceInterface;
use Modules\Authorization\Application\Contracts\CreateRoleServiceInterface;
use Modules\Authorization\Application\Contracts\DeleteRoleServiceInterface;
use Modules\Authorization\Application\Contracts\GetRoleServiceInterface;
use Modules\Authorization\Application\Contracts\ListRolesServiceInterface;
use Modules\Authorization\Application\Contracts\RevokePermissionServiceInterface;
use Modules\Authorization\Application\Contracts\UpdateRoleServiceInterface;
use Modules\Authorization\Application\DTOs\CreateRoleData;
use Modules\Authorization\Application\DTOs\UpdateRoleData;
use Modules\Authorization\Infrastructure\Http\Resources\RoleResource;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class RoleController extends AuthorizedController
{
    public function __construct(
        private readonly CreateRoleServiceInterface $createService,
        private readonly UpdateRoleServiceInterface $updateService,
        private readonly DeleteRoleServiceInterface $deleteService,
        private readonly GetRoleServiceInterface $getService,
        private readonly ListRolesServiceInterface $listService,
        private readonly AssignPermissionServiceInterface $assignPermissionService,
        private readonly RevokePermissionServiceInterface $revokePermissionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', auth()->user()?->tenant_id ?? 0);
        $roles = $this->listService->execute($tenantId);

        return response()->json(['data' => array_map(fn ($r) => (new RoleResource($r))->toArray($request), $roles)]);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->getService->execute($id);

        return (new RoleResource($role))->response();
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $tenantId = (int) ($request->input('tenant_id') ?? $request->header('X-Tenant-ID'));
        $data = new CreateRoleData(
            tenantId: $tenantId,
            name: $request->input('name'),
            slug: $request->input('slug'),
            description: $request->input('description'),
        );

        $role = $this->createService->execute($data);

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:roles,slug,' . $id],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $data = new UpdateRoleData(
            name: $request->input('name'),
            slug: $request->input('slug'),
            description: $request->input('description'),
        );

        $role = $this->updateService->execute($id, $data);

        return (new RoleResource($role))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }

    public function assignPermission(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'permission_id' => ['required', 'integer'],
        ]);

        $this->assignPermissionService->execute($id, (int) $request->input('permission_id'));

        return response()->json(['message' => 'Permission assigned successfully.']);
    }

    public function revokePermission(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'permission_id' => ['required', 'integer'],
        ]);

        $this->revokePermissionService->execute($id, (int) $request->input('permission_id'));

        return response()->json(['message' => 'Permission revoked successfully.']);
    }
}
