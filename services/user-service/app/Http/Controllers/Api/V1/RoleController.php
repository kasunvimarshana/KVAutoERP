<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Role resource controller (v1).
 *
 * Thin controller — delegates all business logic to RoleService.
 */
final class RoleController extends Controller
{
    public function __construct(
        private readonly RoleServiceInterface $roleService,
    ) {}

    /**
     * List roles for the current tenant with pagination.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $claims   = $request->attributes->get('jwt_claims', []);
        $tenantId = (string) ($claims['tenant_id'] ?? '');

        $perPage = (int) $request->query('per_page', config('user_service.pagination.per_page', 20));
        $page    = max(1, (int) $request->query('page', 1));

        $paginator = $this->roleService->listByTenant($tenantId, $perPage, $page);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            RoleResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new role.
     *
     * @param  StoreRoleRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $claims   = $request->attributes->get('jwt_claims', []);
        $tenantId = (string) ($claims['tenant_id'] ?? '');
        $actorId  = (string) ($claims['user_id'] ?? '');

        try {
            $role = $this->roleService->createRole($request->validated(), $tenantId, $actorId);

            return ApiResponse::created(new RoleResource($role), 'Role created successfully.');
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Show a single role.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $role = $this->roleService->findById($id);

        if ($role === null) {
            return ApiResponse::notFound('Role not found.');
        }

        return ApiResponse::success(new RoleResource($role));
    }

    /**
     * Update an existing role.
     *
     * @param  Request  $request
     * @param  string   $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $claims  = $request->attributes->get('jwt_claims', []);
        $actorId = (string) ($claims['user_id'] ?? '');

        $data = $request->validate([
            'name'            => ['nullable', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
            'hierarchy_level' => ['nullable', 'integer', 'min:0', 'max:100'],
            'metadata'        => ['nullable', 'array'],
        ]);

        try {
            $role = $this->roleService->updateRole($id, $data, $actorId);

            return ApiResponse::success(new RoleResource($role), 'Role updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Delete a role.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->roleService->deleteRole($id);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Assign a permission to a role.
     *
     * @param  Request  $request
     * @param  string   $id
     * @return JsonResponse
     */
    public function assignPermission(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'permission_id' => ['required', 'uuid'],
        ]);

        try {
            $this->roleService->assignPermission($id, $data['permission_id']);

            return ApiResponse::success(null, 'Permission assigned to role successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Revoke a permission from a role.
     *
     * @param  string  $id
     * @param  string  $permissionId
     * @return JsonResponse
     */
    public function revokePermission(string $id, string $permissionId): JsonResponse
    {
        try {
            $this->roleService->revokePermission($id, $permissionId);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}
