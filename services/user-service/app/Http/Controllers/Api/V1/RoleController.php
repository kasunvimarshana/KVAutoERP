<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RoleServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RoleServiceContract $roleService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->attributes->get('tenant_id', '');
        $roles    = $this->roleService->listForTenant($tenantId);

        return $this->successResponse($roles);
    }

    public function show(string $id): JsonResponse
    {
        $role = $this->roleService->findById($id);

        if (! $role) {
            return $this->errorResponse('Role not found', [], 404);
        }

        return $this->successResponse($role);
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return $this->successResponse($role, 'Role created successfully', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'slug'        => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string'],
        ]);

        $role = $this->roleService->update($id, $data);

        return $this->successResponse($role, 'Role updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->roleService->delete($id);

        return $this->successResponse(null, 'Role deleted successfully');
    }

    public function assignToUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'   => ['required', 'uuid'],
            'role_id'   => ['required', 'uuid'],
            'tenant_id' => ['sometimes', 'uuid'],
        ]);

        $this->roleService->assignRole(
            $data['user_id'],
            $data['role_id'],
            $data['tenant_id'] ?? null,
        );

        return $this->successResponse(null, 'Role assigned successfully');
    }

    public function revokeFromUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'   => ['required', 'uuid'],
            'role_id'   => ['required', 'uuid'],
            'tenant_id' => ['sometimes', 'uuid'],
        ]);

        $this->roleService->revokeRole(
            $data['user_id'],
            $data['role_id'],
            $data['tenant_id'] ?? null,
        );

        return $this->successResponse(null, 'Role revoked successfully');
    }

    public function permissions(string $id): JsonResponse
    {
        $role = $this->roleService->findById($id);

        if (! $role) {
            return $this->errorResponse('Role not found', [], 404);
        }

        return $this->successResponse($role['permissions'] ?? []);
    }

    public function syncPermissions(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['uuid'],
        ]);

        $this->roleService->syncPermissions($id, $data['permission_ids']);

        return $this->successResponse(null, 'Permissions synced successfully');
    }
}
