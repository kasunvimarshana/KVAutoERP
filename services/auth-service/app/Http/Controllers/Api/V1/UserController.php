<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * User management controller — admin-only operations.
 *
 * POST /api/v1/users — register a new user within the authenticated tenant.
 *
 * Requires the authenticated actor to hold the 'admin' or 'super-admin' role.
 */
final class UserController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly AuthContext $authContext,
    ) {}

    /**
     * Register (create) a new user in the current tenant.
     *
     * POST /api/v1/users
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // ABAC: only admins may register new users.
        if (
            !$this->authContext->hasRole('admin')
            && !$this->authContext->hasRole('super-admin')
        ) {
            return ApiResponse::forbidden('Only administrators can register new users.');
        }

        $tenantId = $this->authContext->getTenantId() ?? '';
        $actorId  = $this->authContext->getUserId() ?? '';

        $data = $request->validated();

        if (
            app(\App\Contracts\Repositories\UserRepositoryInterface::class)
                ->existsByEmailAndTenant($data['email'], $tenantId)
        ) {
            return ApiResponse::error(
                message: 'A user with this email already exists in this tenant.',
                errors: ['email' => ['This email is already registered.']],
                statusCode: 409,
            );
        }

        $user = $this->authService->registerUser($data, $tenantId, $actorId);

        return ApiResponse::created(
            data: new UserResource($user),
            message: 'User registered successfully.',
        );
    }
}
