<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Application\Contracts\AuthServiceInterface;
use Modules\Core\Domain\Exceptions\DomainException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'password'  => 'required|string|min:8',
        ]);

        try {
            $result = $this->authService->register($validated);

            return response()->json([
                'user'  => $this->serializeUser($result['user']),
                'token' => $result['token'],
            ], 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'email'     => 'required|email',
            'password'  => 'required|string',
        ]);

        try {
            $result = $this->authService->login($validated);

            return response()->json([
                'user'  => $this->serializeUser($result['user']),
                'token' => $result['token'],
            ]);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user()->id);

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->me($request->user()->id);

        return response()->json($this->serializeUser($user));
    }

    private function serializeUser(\Modules\Auth\Domain\Entities\User $user): array
    {
        return [
            'id'           => $user->getId(),
            'tenant_id'    => $user->getTenantId(),
            'name'         => $user->getName(),
            'email'        => $user->getEmail(),
            'role'         => $user->getRole(),
            'status'       => $user->getStatus(),
            'preferences'  => $user->getPreferences(),
            'last_login_at'=> $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
            'created_at'   => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
