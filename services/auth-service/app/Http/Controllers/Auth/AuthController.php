<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\Auth\AuthTokenResource;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication Controller.
 *
 * Thin controller: delegates all business logic to AuthService.
 * Handles only request ingestion and response formatting.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Authenticate user and issue access token.
     *
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
            tenantId: $request->attributes->get('tenant_id'),
            deviceInfo: $request->validated('device', []),
        );

        return (new AuthTokenResource($result))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Revoke the current access token.
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Revoke all tokens for the authenticated user (all devices).
     *
     * POST /api/auth/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAllDevices($request->user());

        return response()->json([
            'success' => true,
            'message' => 'All sessions terminated.',
        ]);
    }

    /**
     * Refresh the access token.
     *
     * POST /api/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken($request->user());

        return (new AuthTokenResource($result))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Get the authenticated user profile.
     *
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return (new UserResource($request->user()->load(['roles', 'permissions', 'tenant'])))
            ->response();
    }
}
