<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Auth\Application\Contracts\AssignUserRoleServiceInterface;
use Modules\Auth\Application\Contracts\RevokeUserRoleServiceInterface;
use Modules\Auth\Application\DTOs\AssignUserRoleData;
use Modules\Auth\Infrastructure\Http\Requests\AssignUserRoleRequest;

class UserRoleController extends Controller
{
    public function __construct(
        private readonly AssignUserRoleServiceInterface $assignService,
        private readonly RevokeUserRoleServiceInterface $revokeService,
    ) {}

    public function assign(AssignUserRoleRequest $request, int $userId): JsonResponse
    {
        $validated = $request->validated();
        $data = AssignUserRoleData::fromArray([
            'userId'   => $userId,
            'roleId'   => $validated['role_id'],
            'tenantId' => $validated['tenant_id'],
        ]);

        $userRole = $this->assignService->execute($data);

        return response()->json([
            'data' => [
                'user_id'   => $userRole->userId,
                'role_id'   => $userRole->roleId,
                'tenant_id' => $userRole->tenantId,
            ],
        ], 201);
    }

    public function revoke(int $userId, int $roleId): JsonResponse
    {
        $tenantId = (int) request()->get('tenant_id', 0);
        $this->revokeService->execute($userId, $roleId, $tenantId);

        return response()->json(null, 204);
    }
}
