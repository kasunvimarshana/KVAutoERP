<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\UserProfileServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * UserProfile resource controller (v1).
 *
 * Thin controller — delegates all business logic to UserProfileService.
 */
final class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileServiceInterface $userProfileService,
    ) {}

    /**
     * List user profiles for the current tenant with pagination.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $claims  = $request->attributes->get('jwt_claims', []);
        $tenantId = (string) ($claims['tenant_id'] ?? '');

        $perPage = (int) $request->query('per_page', config('user_service.pagination.per_page', 20));
        $page    = max(1, (int) $request->query('page', 1));

        $paginator = $this->userProfileService->listByTenant($tenantId, $perPage, $page);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            UserProfileResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new user profile.
     *
     * @param  StoreUserProfileRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserProfileRequest $request): JsonResponse
    {
        $claims   = $request->attributes->get('jwt_claims', []);
        $tenantId = (string) ($claims['tenant_id'] ?? '');
        $actorId  = (string) ($claims['user_id'] ?? '');

        try {
            $profile = $this->userProfileService->createProfile(
                $request->validated(),
                $tenantId,
                $actorId,
            );

            return ApiResponse::created(new UserProfileResource($profile), 'User profile created successfully.');
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Show a single user profile.
     *
     * @param  Request  $request
     * @param  string   $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $profile = $this->userProfileService->getProfile($id);

        if ($profile === null) {
            return ApiResponse::notFound('User profile not found.');
        }

        return ApiResponse::success(new UserProfileResource($profile));
    }

    /**
     * Update an existing user profile.
     *
     * @param  UpdateUserProfileRequest  $request
     * @param  string                    $id
     * @return JsonResponse
     */
    public function update(UpdateUserProfileRequest $request, string $id): JsonResponse
    {
        $claims  = $request->attributes->get('jwt_claims', []);
        $actorId = (string) ($claims['user_id'] ?? '');

        try {
            $profile = $this->userProfileService->updateProfile($id, $request->validated(), $actorId);

            return ApiResponse::success(new UserProfileResource($profile), 'User profile updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Deactivate a user profile.
     *
     * @param  Request  $request
     * @param  string   $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $claims  = $request->attributes->get('jwt_claims', []);
        $actorId = (string) ($claims['user_id'] ?? '');

        try {
            $this->userProfileService->deactivateUser($id, $actorId);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Assign a role to a user profile.
     *
     * @param  AssignRoleRequest  $request
     * @param  string             $id
     * @return JsonResponse
     */
    public function assignRole(AssignRoleRequest $request, string $id): JsonResponse
    {
        $claims  = $request->attributes->get('jwt_claims', []);
        $actorId = (string) ($claims['user_id'] ?? '');

        try {
            $this->userProfileService->assignRole($id, $request->validated('role_id'), $actorId);

            return ApiResponse::success(null, 'Role assigned successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Revoke a role from a user profile.
     *
     * @param  Request  $request
     * @param  string   $id
     * @param  string   $roleId
     * @return JsonResponse
     */
    public function revokeRole(Request $request, string $id, string $roleId): JsonResponse
    {
        $claims  = $request->attributes->get('jwt_claims', []);
        $actorId = (string) ($claims['user_id'] ?? '');

        try {
            $this->userProfileService->revokeRole($id, $roleId, $actorId);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}
