<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Contracts\Services\AuthServiceInterface;
use App\Application\DTOs\LoginDTO;
use App\Application\DTOs\RegisterDTO;
use App\Presentation\Requests\LoginRequest;
use App\Presentation\Requests\RegisterRequest;
use App\Presentation\Resources\AuthResource;
use App\Presentation\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Authentication Controller
 * 
 * THIN controller - handles only HTTP request/response.
 * All business logic delegated to AuthService.
 * 
 * Follows strict REST API standards.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    /**
     * POST /api/auth/login
     * 
     * Authenticate a user and receive an access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login(LoginDTO::fromArray($request->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => new AuthResource($token->toArray()),
        ]);
    }

    /**
     * POST /api/auth/register
     * 
     * Register a new user and receive an access token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $token = $this->authService->register(RegisterDTO::fromArray($request->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => new AuthResource($token->toArray()),
        ], 201);
    }

    /**
     * POST /api/auth/logout
     * 
     * Revoke the user's access tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/auth/me
     * 
     * Get the authenticated user's details.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()),
        ]);
    }

    /**
     * POST /api/auth/introspect
     * 
     * Validate a token and return user info (for inter-service auth).
     */
    public function introspect(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->input('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token is required.',
            ], 400);
        }

        $result = $this->authService->introspect($token);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
