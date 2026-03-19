<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\PermissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RolePermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/roles",
     *     summary="Create a new role at runtime (no redeployment needed)",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Role created")
     * )
     */
    public function createRole(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'tenant_id'   => 'required|uuid',
            'permissions' => 'array',
            'description' => 'nullable|string',
        ]);

        $role = $this->permissionService->createRole(
            $request->input('name'),
            $request->input('tenant_id'),
            $request->input('permissions', []),
            $request->input('description', ''),
        );

        return response()->json(['success' => true, 'data' => $role], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/permissions",
     *     summary="Create a new permission at runtime",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Permission created")
     * )
     */
    public function createPermission(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'guard'       => 'nullable|string',
        ]);

        $permission = $this->permissionService->createPermission(
            $request->input('name'),
            $request->input('guard', 'api'),
            $request->input('description', ''),
        );

        return response()->json(['success' => true, 'data' => $permission], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/users/{userId}/roles",
     *     summary="Assign a role to a user",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Role assigned")
     * )
     */
    public function assignRole(Request $request, string $userId): JsonResponse
    {
        $request->validate([
            'role_id'   => 'required|uuid',
            'tenant_id' => 'required|uuid',
        ]);

        $this->permissionService->assignRole($userId, $request->input('role_id'), $request->input('tenant_id'));

        return response()->json(['success' => true, 'message' => 'Role assigned successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/users/{userId}/roles/{roleId}",
     *     summary="Revoke a role from a user",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Role revoked")
     * )
     */
    public function revokeRole(Request $request, string $userId, string $roleId): JsonResponse
    {
        $tenantId = $request->query('tenant_id', $request->attributes->get('jwt_payload.tenant_id', ''));
        $this->permissionService->revokeRole($userId, $roleId, $tenantId);

        return response()->json(['success' => true, 'message' => 'Role revoked successfully.']);
    }
}
