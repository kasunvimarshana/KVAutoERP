<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Internal;

use App\Contracts\UserServiceContract;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal API consumed exclusively by the Auth service.
 * All routes here are protected by VerifyServiceToken middleware.
 */
class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserServiceContract $userService,
    ) {}

    public function findById(string $userId): JsonResponse
    {
        $user = $this->userService->findById($userId);

        if (! $user) {
            return $this->errorResponse('User not found', [], 404);
        }

        return $this->successResponse($user);
    }

    public function findByEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = $this->userService->findByEmail($request->string('email')->toString());

        if (! $user) {
            return $this->errorResponse('User not found', [], 404);
        }

        return $this->successResponse($user);
    }

    public function findByExternalId(Request $request): JsonResponse
    {
        $request->validate([
            'external_id' => ['required', 'string'],
            'provider'    => ['required', 'string'],
        ]);

        $user = $this->userService->findByExternalId(
            $request->string('external_id')->toString(),
            $request->string('provider')->toString(),
        );

        if (! $user) {
            return $this->errorResponse('User not found', [], 404);
        }

        return $this->successResponse($user);
    }

    public function validateCredentials(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $valid = $this->userService->validateCredentials(
            $request->string('user_id')->toString(),
            $request->string('password')->toString(),
        );

        return $this->successResponse(['valid' => $valid]);
    }

    public function getUserClaims(string $userId): JsonResponse
    {
        $claims = $this->userService->getUserClaims($userId);

        if (empty($claims)) {
            return $this->errorResponse('User not found', [], 404);
        }

        return $this->successResponse($claims);
    }

    public function recordLoginEvent(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'    => ['required', 'string'],
            'device_id'  => ['required', 'string'],
            'ip_address' => ['required', 'string'],
        ]);

        $this->userService->recordLoginEvent(
            $request->string('user_id')->toString(),
            $request->string('device_id')->toString(),
            $request->string('ip_address')->toString(),
        );

        return $this->successResponse(null, 'Login event recorded');
    }

    public function incrementTokenVersion(string $userId): JsonResponse
    {
        $newVersion = $this->userService->incrementTokenVersion($userId);

        return $this->successResponse(['token_version' => $newVersion]);
    }
}
