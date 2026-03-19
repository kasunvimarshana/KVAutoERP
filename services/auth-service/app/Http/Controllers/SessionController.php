<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\SessionServiceInterface;
use App\Http\Resources\SessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SessionController extends Controller
{
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
        private readonly AuthServiceInterface $authService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/auth/sessions",
     *     summary="List all active sessions for the authenticated user",
     *     tags={"Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Active sessions list")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload', []);
        $userId = $payload['user_id'] ?? $payload['sub'];

        $sessions = $this->sessionService->getActiveSessions($userId);

        return response()->json([
            'success' => true,
            'data'    => SessionResource::collection($sessions),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/sessions/{sessionId}",
     *     summary="Revoke a specific session",
     *     tags={"Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="sessionId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Session revoked")
     * )
     */
    public function destroy(Request $request, string $sessionId): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload', []);
        $userId = $payload['user_id'] ?? $payload['sub'];

        $this->sessionService->revokeSession($sessionId, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Session revoked successfully.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/sessions/device/{deviceId}",
     *     summary="Revoke the session for a specific device",
     *     tags={"Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="deviceId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Device session revoked")
     * )
     */
    public function destroyDevice(Request $request, string $deviceId): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload', []);
        $userId = $payload['user_id'] ?? $payload['sub'];
        $tenantId = $payload['tenant_id'];

        $this->authService->logoutDevice($userId, $deviceId, $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'Device session revoked successfully.',
        ]);
    }
}
