<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RevocationServiceContract;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RevocationServiceContract $revocationService,
    ) {}

    /**
     * GET /api/v1/auth/sessions
     *
     * List all active device sessions for the authenticated user.
     */
    public function devices(Request $request): JsonResponse
    {
        $userId  = $request->attributes->get('user_id', '');
        $devices = $this->revocationService->getActiveDevices($userId);

        return $this->successResponse(
            data:    $devices,
            message: 'Active sessions retrieved',
        );
    }

    /**
     * DELETE /api/v1/auth/sessions/{deviceId}
     *
     * Revoke a specific device session.
     */
    public function revokeDevice(Request $request, string $deviceId): JsonResponse
    {
        $userId = $request->attributes->get('user_id', '');
        $this->revocationService->revokeDevice($userId, $deviceId);

        return $this->successResponse(message: 'Device session revoked');
    }

    /**
     * DELETE /api/v1/auth/sessions
     *
     * Revoke all device sessions (global logout).
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user_id', '');
        $this->revocationService->revokeAll($userId);

        return $this->successResponse(message: 'All sessions revoked');
    }
}
