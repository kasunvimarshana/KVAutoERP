<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Application\Organization\Commands\CreateOrganizationCommand;
use App\Application\Organization\Services\OrganizationService;
use App\Application\Shared\DTOs\PaginationDTO;
use App\Http\Requests\Organization\CreateOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Support\Api\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class OrganizationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly OrganizationService $organizationService,
    ) {}

    /**
     * GET /api/organizations
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenantId   = $request->header('X-Tenant-ID') ?? $request->query('tenant_id', '');
            $pagination = PaginationDTO::fromArray($request->all());
            $filters    = [];

            if ($request->filled('status')) {
                $filters['where']['status'] = $request->input('status');
            }

            if ($request->filled('search')) {
                $filters['search'] = [
                    'term'    => $request->input('search'),
                    'columns' => ['name', 'slug', 'description'],
                ];
            }

            $result = $this->organizationService->getOrganizations((string) $tenantId, $pagination, $filters);

            if ($result instanceof LengthAwarePaginator) {
                return $this->paginated($result);
            }

            return $this->success(OrganizationResource::collection($result));
        } catch (Throwable $e) {
            Log::error('Failed to list organizations', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to retrieve organizations.');
        }
    }

    /**
     * POST /api/organizations
     */
    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        try {
            $command = new CreateOrganizationCommand(
                tenantId:    $request->input('tenant_id'),
                name:        $request->string('name')->trim()->toString(),
                slug:        $request->string('slug', '')->trim()->toString(),
                parentId:    $request->input('parent_id'),
                description: $request->input('description'),
                status:      $request->input('status', 'active'),
                settings:    $request->input('settings', []),
                metadata:    $request->input('metadata', []),
            );

            $dto = $this->organizationService->createOrganization($command);

            return $this->created(new OrganizationResource((object) (array) $dto), 'Organization created.');
        } catch (InvalidArgumentException $e) {
            return $this->unprocessable(null, $e->getMessage());
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to create organization', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to create organization.');
        }
    }

    /**
     * GET /api/organizations/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $dto = $this->organizationService->getOrganization($id);

            return $this->success(new OrganizationResource((object) (array) $dto));
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            return $this->serverError('Failed to retrieve organization.');
        }
    }

    /**
     * PUT /api/organizations/{id}
     */
    public function update(UpdateOrganizationRequest $request, string $id): JsonResponse
    {
        try {
            $data = array_filter([
                'name'        => $request->input('name'),
                'slug'        => $request->input('slug'),
                'parent_id'   => $request->input('parent_id'),
                'description' => $request->input('description'),
                'status'      => $request->input('status'),
                'settings'    => $request->input('settings'),
                'metadata'    => $request->input('metadata'),
            ], fn ($v) => $v !== null);

            $dto = $this->organizationService->updateOrganization($id, $data);

            return $this->success(new OrganizationResource((object) (array) $dto), 'Organization updated.');
        } catch (InvalidArgumentException $e) {
            return $this->unprocessable(null, $e->getMessage());
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to update organization', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to update organization.');
        }
    }

    /**
     * DELETE /api/organizations/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->organizationService->deleteOrganization($id);

            return $this->noContent();
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to delete organization', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to delete organization.');
        }
    }

    /**
     * GET /api/organizations/{id}/hierarchy
     */
    public function hierarchy(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id', '');
            $tree     = $this->organizationService->getHierarchy((string) $tenantId);

            return $this->success(
                array_map(fn ($dto) => new OrganizationResource((object) (array) $dto), $tree),
                'Organization hierarchy retrieved.'
            );
        } catch (Throwable $e) {
            Log::error('Failed to get hierarchy', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to retrieve hierarchy.');
        }
    }
}
