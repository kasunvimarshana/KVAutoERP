<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Auth\Commands\LoginCommand;
use App\Application\Auth\Commands\LogoutCommand;
use App\Application\Auth\Commands\RefreshTokenCommand;
use App\Application\Auth\Commands\RegisterCommand;
use App\Application\Auth\Queries\GetUsersQuery;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Shared\Base\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Auth HTTP Controller.
 *
 * Thin controller — delegates all business logic to {@see AuthService}.
 * Responsible only for parsing HTTP input, dispatching commands/queries,
 * and formatting the HTTP response.
 */
final class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // Public endpoints
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Authenticate a user and issue tokens.
     *
     * POST /auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $command = new LoginCommand(
            email: $request->validated('email'),
            password: $request->validated('password'),
            tenantId: $request->tenantId() ?? $request->header('X-Tenant-ID', ''),
            deviceInfo: [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? '',
            ],
        );

        $result = $this->authService->login($command);

        return $this->success($result, 'Login successful');
    }

    /**
     * Register a new user account.
     *
     * POST /auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $command = new RegisterCommand(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            tenantId: $request->tenantId() ?? $request->header('X-Tenant-ID', ''),
        );

        $result = $this->authService->register($command);

        return $this->created($result, 'User registered successfully');
    }

    /**
     * Revoke the authenticated user's token(s).
     *
     * POST /auth/logout  (requires auth)
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $command = new LogoutCommand(
            userId: (string) $user->getAuthIdentifier(),
            tenantId: $request->header('X-Tenant-ID', ''),
            allDevices: (bool) $request->input('all_devices', false),
        );

        $this->authService->logout($command);

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Exchange a refresh token for a new access token.
     *
     * POST /auth/refresh
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $command = new RefreshTokenCommand(
            refreshToken: $request->validated('refresh_token'),
            tenantId: $request->header('X-Tenant-ID', ''),
        );

        $result = $this->authService->refresh($command);

        return $this->success($result, 'Token refreshed successfully');
    }

    /**
     * Return the currently authenticated user's profile.
     *
     * GET /auth/me  (requires auth)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->authService->getUser(
            userId: (string) $user->getAuthIdentifier(),
            tenantId: $request->header('X-Tenant-ID', ''),
        );

        return $this->success($result);
    }

    /**
     * Return a paginated list of users for the current tenant.
     *
     * GET /users  (requires auth + admin role)
     */
    public function users(Request $request): JsonResponse
    {
        $query = new GetUsersQuery(
            tenantId: $request->header('X-Tenant-ID', ''),
            filters: $request->only(['is_active', 'search']),
            sorts: $request->input('sorts', ['created_at' => 'desc']),
            perPage: (int) $request->input('per_page', 15),
            page: (int) $request->input('page', 1),
        );

        $result = $this->authService->getUsers($query);

        if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return $this->paginated($result);
        }

        return $this->success($result);
    }
}
