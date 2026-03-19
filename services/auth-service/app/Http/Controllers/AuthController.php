<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\AuthServiceInterface;
use App\DTOs\LoginCredentialsDto;
use App\DTOs\LogoutContextDto;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Authenticate user and issue JWT token pair",
     *     tags={"Authentication"},
     *     @OA\RequestBody(ref="#/components/requestBodies/LoginRequest"),
     *     @OA\Response(response=200, description="Login successful", @OA\JsonContent(ref="#/components/schemas/AuthTokenResponse")),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=423, description="Account locked"),
     *     @OA\Response(response=429, description="Too many requests")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = LoginCredentialsDto::fromArray([
            'email'           => $request->input('email'),
            'password'        => $request->input('password'),
            'tenant_id'       => $request->input('tenant_id'),
            'device_id'       => $request->input('device_id'),
            'device_name'     => $request->input('device_name', 'Unknown Device'),
            'ip_address'      => $request->ip(),
            'user_agent'      => $request->userAgent() ?? '',
            'organisation_id' => $request->input('organisation_id'),
            'branch_id'       => $request->input('branch_id'),
            'remember_me'     => $request->boolean('remember_me'),
        ]);

        $result = $this->authService->login($credentials);

        return response()->json([
            'success' => true,
            'data'    => new AuthTokenResource($result),
            'message' => 'Login successful.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout current device session",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=204, description="Logout successful"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(LogoutRequest $request): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload', []);

        $context = new LogoutContextDto(
            userId: $payload['user_id'] ?? $payload['sub'],
            tenantId: $payload['tenant_id'],
            sessionId: $request->attributes->get('session_id', ''),
            accessTokenJti: $payload['jti'],
            accessTokenRemainingTtlSeconds: $request->attributes->get('token_remaining_ttl', 0),
            deviceId: $payload['device_id'],
            ipAddress: $request->ip(),
        );

        $this->authService->logout($context);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout/all",
     *     summary="Logout all devices (global logout)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="All sessions revoked")
     * )
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload', []);

        $this->authService->logoutAllDevices(
            $payload['user_id'] ?? $payload['sub'],
            $payload['tenant_id'],
        );

        return response()->json([
            'success' => true,
            'message' => 'All sessions revoked successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Refresh access token using a valid refresh token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(ref="#/components/requestBodies/RefreshTokenRequest"),
     *     @OA\Response(response=200, description="Tokens refreshed"),
     *     @OA\Response(response=401, description="Invalid or expired refresh token")
     * )
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $tokenPair = $this->authService->refreshTokens(
            $request->input('refresh_token'),
            $request->input('device_id'),
        );

        return response()->json([
            'success' => true,
            'data'    => $tokenPair->toArray(),
            'message' => 'Tokens refreshed successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register a new user under a tenant",
     *     tags={"Authentication"},
     *     @OA\RequestBody(ref="#/components/requestBodies/RegisterRequest"),
     *     @OA\Response(response=201, description="Registration successful")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            array_merge($request->validated(), [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? '',
            ]),
            $request->input('tenant_id'),
        );

        return response()->json([
            'success' => true,
            'data'    => new AuthTokenResource($result),
            'message' => 'Registration successful.',
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Get authenticated user profile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User profile")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $payload = $request->attributes->get('jwt_payload', []);
        $user = \App\Models\User::with(['roles.permissions', 'directPermissions'])->find($payload['user_id'] ?? $payload['sub']);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ]);
    }
}
