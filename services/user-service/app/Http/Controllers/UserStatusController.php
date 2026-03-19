<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserStatusController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {}

    /**
     * POST /api/v1/users/{id}/activate
     * Activate a user account.
     */
    public function activate(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $user = $this->userService->toggleUserStatus($id, $tenantId, true);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
            'message' => 'User activated successfully.',
        ]);
    }

    /**
     * POST /api/v1/users/{id}/deactivate
     * Deactivate a user account.
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $user = $this->userService->toggleUserStatus($id, $tenantId, false);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
            'message' => 'User deactivated successfully.',
        ]);
    }
}
