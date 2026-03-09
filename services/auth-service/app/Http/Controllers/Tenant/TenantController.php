<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenant\Services\TenantService;
use App\Http\Requests\Tenant\CreateTenantRequest;
use App\Http\Requests\Tenant\ListTenantsRequest;
use App\Http\Requests\Tenant\UpdateTenantConfigRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\Tenant\TenantCollection;
use App\Http\Resources\Tenant\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Controller.
 *
 * Thin controller: delegates all business logic to TenantService.
 * Handles request ingestion and response formatting only.
 */
class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    /**
     * List all tenants with filtering, sorting, and conditional pagination.
     *
     * GET /api/tenants
     */
    public function index(ListTenantsRequest $request): JsonResponse
    {
        $tenants = $this->tenantService->list($request->validated());

        return (new TenantCollection($tenants))->response();
    }

    /**
     * Get a specific tenant by ID.
     *
     * GET /api/tenants/{id}
     */
    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantService->getById($id);

        return (new TenantResource($tenant))->response();
    }

    /**
     * Create a new tenant.
     *
     * POST /api/tenants
     */
    public function store(CreateTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->create($request->validated());

        return (new TenantResource($tenant))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update an existing tenant.
     *
     * PUT /api/tenants/{id}
     */
    public function update(UpdateTenantRequest $request, string $id): JsonResponse
    {
        $tenant = $this->tenantService->update($id, $request->validated());

        return (new TenantResource($tenant))->response();
    }

    /**
     * Update tenant's runtime configuration without restart.
     *
     * PATCH /api/tenants/{id}/configuration
     */
    public function updateConfiguration(UpdateTenantConfigRequest $request, string $id): JsonResponse
    {
        $tenant = $this->tenantService->updateRuntimeConfiguration($id, $request->validated('configuration'));

        return (new TenantResource($tenant))->response();
    }

    /**
     * Delete a tenant.
     *
     * DELETE /api/tenants/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->tenantService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully.',
        ]);
    }
}
