<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->login($request->only('email', 'password'));

        if (!$result) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json($result);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        return response()->json($this->authService->register($request->all()), 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Successfully logged out.']);
    }

    public function refresh(Request $request): JsonResponse
    {
        return response()->json($this->authService->refresh($request->user()));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('tenant', 'roles', 'permissions'));
    }

    /** SSO: provide a cross-service token to the authenticated user. */
    public function ssoToken(Request $request): JsonResponse
    {
        return response()->json([
            'sso_token' => $this->authService->generateSsoToken($request->user()),
        ]);
    }
}
