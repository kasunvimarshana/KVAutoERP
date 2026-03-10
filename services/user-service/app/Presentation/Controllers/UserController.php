<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Contracts\Services\UserServiceInterface;
use App\Application\DTOs\UserProfileDTO;
use App\Presentation\Requests\CreateUserRequest;
use App\Presentation\Requests\UpdateUserRequest;
use App\Presentation\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {}

    /**
     * GET /api/users
     * List all users with filtering, sorting, searching, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $params = array_merge($request->query(), ['tenant_id' => $tenantId]);
        $users = $this->userService->getAllUsers($params);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/users/{userId}
     * Get a single user profile.
     */
    public function show(Request $request, string $userId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $profile = $this->userService->getUserProfile($userId, $tenantId);

        return response()->json([
            'success' => true,
            'data' => new UserResource($profile),
        ]);
    }

    /**
     * POST /api/users
     * Create a new user profile.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $data = array_merge($request->validated(), ['tenant_id' => $tenantId]);
        $profile = $this->userService->createProfile(UserProfileDTO::fromArray($data));

        return response()->json([
            'success' => true,
            'message' => 'User profile created successfully.',
            'data' => new UserResource($profile),
        ], 201);
    }

    /**
     * PUT /api/users/{userId}
     * Update an existing user profile.
     */
    public function update(UpdateUserRequest $request, string $userId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $profile = $this->userService->updateProfile($userId, $request->validated(), $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'User profile updated successfully.',
            'data' => new UserResource($profile),
        ]);
    }

    /**
     * DELETE /api/users/{userId}
     * Soft-delete a user profile.
     */
    public function destroy(Request $request, string $userId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $this->userService->deleteProfile($userId, $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'User profile deleted successfully.',
        ]);
    }

    /**
     * POST /api/users/{userId}/roles
     * Assign a role to a user.
     */
    public function assignRole(Request $request, string $userId): JsonResponse
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $profile = $this->userService->assignRole($userId, $request->input('role'), $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully.',
            'data' => new UserResource($profile),
        ]);
    }

    /**
     * GET /api/users/{userId}/permissions
     * Get all permissions for a user (role-based + individual ABAC).
     */
    public function permissions(Request $request, string $userId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $permissions = $this->userService->getUserPermissions($userId, $tenantId);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $userId,
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * POST /api/users/{userId}/activate
     */
    public function activate(Request $request, string $userId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $profile = $this->userService->activateUser($userId, $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'User activated.',
            'data' => new UserResource($profile),
        ]);
    }

    /**
     * POST /api/users/{userId}/deactivate
     */
    public function deactivate(Request $request, string $userId): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $profile = $this->userService->deactivateUser($userId, $tenantId);

        return response()->json([
            'success' => true,
            'message' => 'User deactivated.',
            'data' => new UserResource($profile),
        ]);
    }
}
