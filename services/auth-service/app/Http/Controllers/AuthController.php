<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthService;
use App\Application\Services\RBACService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RBACService $rbacService,
    ) {}

    // -------------------------------------------------------------------------
    // Standard auth
    // -------------------------------------------------------------------------

    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'message'      => 'Login successful',
            'token_type'   => $result['token_type'],
            'access_token' => $result['access_token'],
            'expires_at'   => $result['expires_at'],
            'user'         => new UserResource($result['user']),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refresh($request->user());

        return response()->json([
            'message'      => 'Token refreshed',
            'token_type'   => 'Bearer',
            'access_token' => $result['access_token'],
            'expires_at'   => $result['expires_at'],
        ]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->me($request->user());

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'message'      => 'Registration successful',
            'token_type'   => $result['token_type'],
            'access_token' => $result['access_token'],
            'expires_at'   => $result['expires_at'],
            'user'         => new UserResource($result['user']),
        ], 201);
    }

    // -------------------------------------------------------------------------
    // SSO
    // -------------------------------------------------------------------------

    /**
     * POST /api/v1/auth/sso-token
     * Issues a short-lived SSO token for cross-service use.
     */
    public function ssoToken(Request $request): JsonResponse
    {
        $result = $this->authService->ssoToken($request->user());

        return response()->json([
            'message'    => 'SSO token issued',
            'sso_token'  => $result['sso_token'],
            'expires_at' => $result['expires_at'],
            'claims'     => $result['claims'],
        ]);
    }

    /**
     * POST /api/v1/auth/validate-token
     * Validates a bearer token and returns its decoded claims (used by other services).
     */
    public function validateToken(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->input('token');

        if (!$token) {
            return response()->json(['message' => 'No token provided'], 422);
        }

        $result = $this->authService->verifyToken($token);

        return response()->json(['data' => $result]);
    }

    // -------------------------------------------------------------------------
    // RBAC management
    // -------------------------------------------------------------------------

    /**
     * POST /api/v1/users/{id}/roles
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role' => 'required|string']);

        $user = \App\Domain\Auth\Entities\User::forTenant($request->header('X-Tenant-ID'))->findOrFail($id);
        $this->rbacService->assignRole($user, $request->input('role'));

        return response()->json([
            'message' => 'Role assigned successfully',
            'roles'   => $this->rbacService->getUserRoles($user),
        ]);
    }

    /**
     * DELETE /api/v1/users/{id}/roles/{role}
     */
    public function revokeRole(Request $request, int $id, string $role): JsonResponse
    {
        $user = \App\Domain\Auth\Entities\User::forTenant($request->header('X-Tenant-ID'))->findOrFail($id);
        $this->rbacService->revokeRole($user, $role);

        return response()->json([
            'message' => 'Role revoked successfully',
            'roles'   => $this->rbacService->getUserRoles($user),
        ]);
    }

    /**
     * GET /api/v1/users/{id}/permissions
     */
    public function getUserPermissions(Request $request, int $id): JsonResponse
    {
        $user = \App\Domain\Auth\Entities\User::forTenant($request->header('X-Tenant-ID'))->findOrFail($id);

        return response()->json([
            'data' => [
                'roles'       => $this->rbacService->getUserRoles($user),
                'permissions' => $this->rbacService->getUserPermissions($user),
            ],
        ]);
    }
}
