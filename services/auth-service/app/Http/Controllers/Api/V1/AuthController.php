<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\AuthServiceInterface;
use App\Exceptions\AccountInactiveException;
use App\Exceptions\AuthenticationException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RevokeTokenRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Services\AuthContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Auth controller — login, logout, token refresh, me, session revocation.
 *
 * All methods are intentionally thin: input validation lives in Request
 * classes, business logic lives in AuthService.
 */
final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly AuthContext $authContext,
    ) {}

    /**
     * Authenticate a user and issue a JWT access + refresh token pair.
     *
     * POST /api/v1/auth/login
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $tokenPair = $this->authService->login(
                email: $request->validated('email'),
                password: $request->validated('password'),
                tenantId: $request->validated('tenant_id'),
                deviceId: $request->validated('device_id'),
                ipAddress: $request->ip() ?? '0.0.0.0',
                userAgent: $request->userAgent() ?? '',
            );

            return ApiResponse::success(
                data: new AuthTokenResource($tokenPair),
                message: 'Login successful.',
                statusCode: 200,
            );
        } catch (AuthenticationException $e) {
            return ApiResponse::unauthorized($e->getMessage());
        } catch (AccountInactiveException $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    /**
     * Revoke the current access token (logout).
     *
     * POST /api/v1/auth/logout
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $rawToken = (string) $request->attributes->get('raw_token', '');

        $this->authService->logout(
            accessToken: $rawToken,
            ipAddress: $request->ip() ?? '0.0.0.0',
            userAgent: $request->userAgent() ?? '',
        );

        return ApiResponse::success(message: 'Logged out successfully.');
    }

    /**
     * Rotate a refresh token and issue a new access + refresh token pair.
     *
     * POST /api/v1/auth/refresh
     *
     * @param  RefreshTokenRequest  $request
     * @return JsonResponse
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $tokenPair = $this->authService->refreshTokens(
                refreshToken: $request->validated('refresh_token'),
                deviceId: $request->validated('device_id'),
                ipAddress: $request->ip() ?? '0.0.0.0',
                userAgent: $request->userAgent() ?? '',
            );

            return ApiResponse::success(
                data: new AuthTokenResource($tokenPair),
                message: 'Token refreshed successfully.',
            );
        } catch (InvalidRefreshTokenException $e) {
            return ApiResponse::unauthorized($e->getMessage());
        }
    }

    /**
     * Return the authenticated user's profile.
     *
     * GET /api/v1/auth/me
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $claims */
        $claims = $request->attributes->get('jwt_claims', []);

        $user = $this->authService->getUserFromClaims($claims);

        if ($user === null) {
            return ApiResponse::notFound('Authenticated user not found.');
        }

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'User profile retrieved.',
        );
    }

    /**
     * Revoke all sessions for a specific device.
     *
     * POST /api/v1/auth/revoke-device
     *
     * @param  RevokeTokenRequest  $request
     * @return JsonResponse
     */
    public function revokeDevice(RevokeTokenRequest $request): JsonResponse
    {
        $userId   = $this->authContext->getUserId() ?? '';
        $deviceId = $request->validated('device_id');

        $this->authService->revokeDeviceSession(
            userId: $userId,
            deviceId: $deviceId,
            ipAddress: $request->ip() ?? '0.0.0.0',
            userAgent: $request->userAgent() ?? '',
        );

        return ApiResponse::success(message: 'Device session revoked.');
    }

    /**
     * Revoke all sessions for the authenticated user (global logout).
     *
     * POST /api/v1/auth/revoke-all
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $userId = $this->authContext->getUserId() ?? '';

        $this->authService->revokeAllSessions(
            userId: $userId,
            ipAddress: $request->ip() ?? '0.0.0.0',
            userAgent: $request->userAgent() ?? '',
        );

        return ApiResponse::success(message: 'All sessions revoked successfully.');
    }
}
