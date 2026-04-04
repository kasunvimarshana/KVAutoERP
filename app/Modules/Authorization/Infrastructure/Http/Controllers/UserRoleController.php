<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authorization\Application\Contracts\AssignRoleToUserServiceInterface;
use Modules\Authorization\Application\Contracts\CheckUserPermissionServiceInterface;
use Modules\Authorization\Application\Contracts\GetUserPermissionsServiceInterface;
use Modules\Authorization\Application\Contracts\RevokeRoleFromUserServiceInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;
use Modules\Authorization\Infrastructure\Http\Resources\PermissionResource;
use Modules\Authorization\Infrastructure\Http\Resources\RoleResource;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class UserRoleController extends AuthorizedController
{
    public function __construct(
        private readonly AssignRoleToUserServiceInterface $assignRoleService,
        private readonly RevokeRoleFromUserServiceInterface $revokeRoleService,
        private readonly GetUserPermissionsServiceInterface $getUserPermissionsService,
        private readonly CheckUserPermissionServiceInterface $checkUserPermissionService,
        private readonly UserRoleRepositoryInterface $userRoleRepository,
    ) {}

    public function assignRole(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'role_id' => ['required', 'integer'],
            'tenant_id' => ['sometimes', 'integer'],
        ]);

        $tenantId = (int) ($request->input('tenant_id') ?? $request->header('X-Tenant-ID'));
        $this->assignRoleService->execute($userId, (int) $request->input('role_id'), $tenantId);

        return response()->json(['message' => 'Role assigned successfully.']);
    }

    public function revokeRole(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'role_id' => ['required', 'integer'],
            'tenant_id' => ['sometimes', 'integer'],
        ]);

        $tenantId = (int) ($request->input('tenant_id') ?? $request->header('X-Tenant-ID'));
        $this->revokeRoleService->execute($userId, (int) $request->input('role_id'), $tenantId);

        return response()->json(['message' => 'Role revoked successfully.']);
    }

    public function getUserRoles(Request $request, int $userId): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', auth()->user()?->tenant_id ?? 0);
        $roles = $this->userRoleRepository->getUserRoles($userId, $tenantId);

        return response()->json([
            'data' => array_map(fn ($r) => (new RoleResource($r))->toArray($request), $roles),
        ]);
    }

    public function getUserPermissions(Request $request, int $userId): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', auth()->user()?->tenant_id ?? 0);
        $permissions = $this->getUserPermissionsService->execute($userId, $tenantId);

        return response()->json([
            'data' => array_map(fn ($p) => (new PermissionResource($p))->toArray($request), $permissions),
        ]);
    }

    public function checkPermission(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'permission' => ['required', 'string'],
            'tenant_id' => ['sometimes', 'integer'],
        ]);

        $tenantId = (int) ($request->input('tenant_id') ?? $request->header('X-Tenant-ID'));
        $has = $this->checkUserPermissionService->execute($userId, $tenantId, $request->input('permission'));

        return response()->json(['has_permission' => $has]);
    }
}
