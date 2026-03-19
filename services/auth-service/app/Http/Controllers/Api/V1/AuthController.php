<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AuthServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\ServiceTokenRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\TokenResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthServiceContract $authService,
    ) {}

    /**
     * POST /api/v1/auth/login
     *
     * Authenticate a user via local credentials or a federated IAM provider.
     * Returns a JWT access token + opaque refresh token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            credentials: $request->validated(),
            deviceId:    $request->header('X-Device-Id', $request->ip()),
            ipAddress:   $request->ip(),
        );

        return $this->successResponse(
            data:    (new AuthResource($result))->toArray($request),
            message: 'Login successful',
        );
    }

    /**
     * POST /api/v1/auth/logout
     *
     * Revoke the current token (and optionally all device tokens).
     */
    public function logout(LogoutRequest $request): JsonResponse
    {
        $this->authService->logout(
            accessToken: $request->bearerToken() ?? '',
            deviceId:    $request->header('X-Device-Id'),
            allDevices:  $request->boolean('all_devices', false),
        );

        return $this->successResponse(message: 'Logout successful');
    }

    /**
     * POST /api/v1/auth/refresh
     *
     * Rotate the refresh token and issue a new access + refresh pair.
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $result = $this->authService->refreshToken(
            refreshToken: $request->validated('refresh_token'),
            deviceId:     $request->header('X-Device-Id', $request->ip()),
        );

        return $this->successResponse(
            data:    (new TokenResource($result))->toArray($request),
            message: 'Token refreshed',
        );
    }

    /**
     * GET /api/v1/auth/verify
     *
     * Verify an access token and return the decoded claims.
     * Used by downstream services that prefer synchronous verification.
     */
    public function verify(Request $request): JsonResponse
    {
        $claims = $this->authService->verifyToken(
            $request->bearerToken() ?? ''
        );

        return $this->successResponse(
            data:    $claims->toArray(),
            message: 'Token is valid',
        );
    }

    /**
     * GET /api/v1/auth/public-key
     *
     * Return the RSA public key used to verify JWT signatures.
     * All other microservices fetch this once on startup for local verification.
     */
    public function publicKey(): JsonResponse
    {
        $key = app(\App\Contracts\TokenServiceContract::class)->getPublicKey();

        return $this->successResponse(
            data: [
                'public_key' => $key,
                'algorithm'  => 'RS256',
            ],
            message: 'Public key retrieved',
        );
    }

    /**
     * GET /api/v1/auth/.well-known/jwks.json
     *
     * Return the public key as a JSON Web Key Set (JWKS).
     * Microservices and third-party tools can use this standards-compliant
     * endpoint to auto-discover the key for local JWT signature verification.
     */
    public function jwks(): JsonResponse
    {
        $jwks = app(\App\Contracts\TokenServiceContract::class)->getJwks();

        return response()->json($jwks);
    }

    /**
     * POST /api/v1/auth/service-token
     *
     * Issue a short-lived JWT for service-to-service authentication.
     * The calling service must present a valid service_id and service_secret
     * that are pre-registered in config/auth.php (service_credentials).
     *
     * This endpoint is rate-limited and should only be called by internal services.
     */
    public function serviceToken(ServiceTokenRequest $request): JsonResponse
    {
        $result = $this->authService->issueServiceToken(
            serviceId:     $request->validated('service_id'),
            serviceSecret: $request->validated('service_secret'),
        );

        return $this->successResponse(
            data:    (new TokenResource($result))->toArray($request),
            message: 'Service token issued',
        );
    }
}
