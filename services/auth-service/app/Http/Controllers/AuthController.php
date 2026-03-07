<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                credentials: $request->only('email', 'password'),
                tenantId:    $request->input('tenant_id'),
            );

            return $this->successResponse($result, 'Login successful.', 200);
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 401);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->successResponse($result, 'Registration successful.', 201);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Logged out successfully.', 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken === null) {
            return $this->errorResponse('No token provided.', 401);
        }

        try {
            $result = $this->authService->refresh($bearerToken);

            return $this->successResponse($result, 'Token refreshed successfully.', 200);
        } catch (AuthenticationException $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user()->load('tenant');
        $tenant = $user->tenant;

        return $this->successResponse([
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'permissions' => $user->permissions ?? [],
                'status'      => $user->status,
                'tenant_id'   => $user->tenant_id,
            ],
            'tenant' => $tenant ? [
                'id'     => $tenant->id,
                'name'   => $tenant->name,
                'slug'   => $tenant->slug,
                'plan'   => $tenant->plan,
                'status' => $tenant->status,
            ] : null,
        ], 'User profile retrieved.', 200);
    }

    private function successResponse(mixed $data, string $message, int $status): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    private function errorResponse(string $message, int $status, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
