<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\PermissionServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Permission resource controller (v1).
 *
 * Thin controller — delegates all business logic to PermissionService.
 */
final class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
    ) {}

    /**
     * List permissions for the current tenant with pagination.
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

        $paginator = $this->permissionService->listByTenant($tenantId, $perPage, $page);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            PermissionResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new permission.
     *
     * @param  StorePermissionRequest  $request
     * @return JsonResponse
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $claims   = $request->attributes->get('jwt_claims', []);
        $tenantId = (string) ($claims['tenant_id'] ?? '');
        $actorId  = (string) ($claims['user_id'] ?? '');

        try {
            $permission = $this->permissionService->createPermission($request->validated(), $tenantId, $actorId);

            return ApiResponse::created(new PermissionResource($permission), 'Permission created successfully.');
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Show a single permission.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $permission = $this->permissionService->findById($id);

        if ($permission === null) {
            return ApiResponse::notFound('Permission not found.');
        }

        return ApiResponse::success(new PermissionResource($permission));
    }

    /**
     * Update an existing permission.
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
            'name'        => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'metadata'    => ['nullable', 'array'],
        ]);

        try {
            $permission = $this->permissionService->updatePermission($id, $data, $actorId);

            return ApiResponse::success(new PermissionResource($permission), 'Permission updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Delete a permission.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->permissionService->deletePermission($id);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}
