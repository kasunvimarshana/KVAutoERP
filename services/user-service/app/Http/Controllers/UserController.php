<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\DTOs\UserProfileDto;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollectionResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {}

    /**
     * GET /api/v1/users
     * List all users for the authenticated tenant (paginated, filterable).
     */
    public function index(Request $request): UserCollectionResource
    {
        $tenantId = $request->attributes->get('tenant_id');

        $filters = $request->only(['name', 'email', 'is_active', 'search']);

        $paginator = $this->userService->searchUsers(
            tenantId: $tenantId,
            filters: $filters,
            perPage: (int) $request->query('per_page', 15),
        );

        return new UserCollectionResource($paginator);
    }

    /**
     * POST /api/v1/users
     * Create a new user under the authenticated tenant.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $dto = CreateUserDto::fromArray(
            array_merge($request->validated(), ['tenant_id' => $tenantId])
        );

        $user = $this->userService->createUser($dto);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
            'message' => 'User created successfully.',
        ], 201);
    }

    /**
     * GET /api/v1/users/{id}
     * Show a single user.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $user = $this->userService->getUser($id, $tenantId);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user->loadMissing('profile')),
            'message' => 'User retrieved successfully.',
        ]);
    }

    /**
     * PUT /api/v1/users/{id}
     * Update a user's core fields.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $dto = UpdateUserDto::fromArray($request->validated());

        $user = $this->userService->updateUser($id, $tenantId, $dto);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
            'message' => 'User updated successfully.',
        ]);
    }

    /**
     * DELETE /api/v1/users/{id}
     * Soft-delete a user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $this->userService->deleteUser($id, $tenantId);

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * GET /api/v1/users/{id}/profile
     * Retrieve a user's extended profile.
     */
    public function profile(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $profile = $this->userService->getUserProfile($id, $tenantId);

        return response()->json([
            'success' => true,
            'data'    => new UserProfileResource($profile),
            'message' => 'User profile retrieved successfully.',
        ]);
    }

    /**
     * PUT /api/v1/users/{id}/profile
     * Create or update a user's extended profile.
     */
    public function updateProfile(UpdateProfileRequest $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $dto = UserProfileDto::fromArray($request->validated());

        $profile = $this->userService->updateUserProfile($id, $tenantId, $dto);

        return response()->json([
            'success' => true,
            'data'    => new UserProfileResource($profile),
            'message' => 'User profile updated successfully.',
        ]);
    }

    /**
     * POST /api/v1/users/{id}/password
     * Change a user's password.
     */
    public function changePassword(ChangePasswordRequest $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');

        $this->userService->changePassword(
            userId: $id,
            tenantId: $tenantId,
            currentPassword: $request->validated('current_password'),
            newPassword: $request->validated('new_password'),
        );

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Password changed successfully.',
        ]);
    }
}
