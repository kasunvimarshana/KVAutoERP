<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Tenant\Commands\CreateTenantCommand;
use App\Application\Tenant\Commands\DeleteTenantCommand;
use App\Application\Tenant\Commands\UpdateTenantCommand;
use App\Application\Tenant\Queries\GetTenantQuery;
use App\Application\Tenant\Queries\ListTenantsQuery;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;
use App\Shared\Base\BaseController;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant Controller.
 *
 * Exposes CRUD operations for tenant management.
 * All responses use the standard KV_SAAS JSON envelope via BaseController.
 */
final class TenantController extends BaseController
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    /**
     * GET /tenants
     *
     * Return a paginated list of tenants.
     */
    public function index(Request $request): JsonResponse
    {
        $query = new ListTenantsQuery(
            filters: $request->only(['plan', 'is_active']),
            sorts: $request->input('sorts', ['created_at' => 'desc']),
            perPage: (int) $request->input('per_page', 15),
            page: (int) $request->input('page', 1),
            includeInactive: (bool) $request->input('include_inactive', false),
        );

        $result = $this->tenantService->listTenants($query);

        if ($result instanceof LengthAwarePaginator) {
            $result->getCollection()->transform(
                fn (array $t) => (new TenantResource((object) $t))->resolve()
            );
            return $this->paginated($result);
        }

        $data = array_map(
            fn (array $t) => (new TenantResource((object) $t))->resolve(),
            $result
        );

        return $this->success($data);
    }

    /**
     * GET /tenants/{id}
     *
     * Return a single tenant by UUID.
     */
    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantService->getTenant($id);

        if ($tenant === null) {
            return $this->notFound('Tenant not found.');
        }

        return $this->success((new TenantResource((object) $tenant))->resolve());
    }

    /**
     * POST /tenants
     *
     * Create a new tenant and provision its database.
     */
    public function store(CreateTenantRequest $request): JsonResponse
    {
        $command = new CreateTenantCommand(
            name: $request->input('name'),
            slug: $request->input('slug'),
            domain: $request->input('domain'),
            plan: $request->input('plan'),
            billingEmail: $request->input('billing_email'),
            adminEmail: $request->input('admin_email'),
            settings: $request->input('settings', []),
        );

        $tenant = $this->tenantService->createTenant($command);

        return $this->created(
            (new TenantResource((object) $tenant))->resolve(),
            'Tenant created and provisioning started.',
        );
    }

    /**
     * PUT /tenants/{id}
     *
     * Update an existing tenant.
     */
    public function update(UpdateTenantRequest $request, string $id): JsonResponse
    {
        $command = new UpdateTenantCommand(
            tenantId: $id,
            name: $request->input('name'),
            domain: $request->input('domain'),
            plan: $request->input('plan'),
            billingEmail: $request->input('billing_email'),
            isActive: $request->has('is_active') ? (bool) $request->input('is_active') : null,
            settings: $request->input('settings'),
        );

        $tenant = $this->tenantService->updateTenant($command);

        return $this->success(
            (new TenantResource((object) $tenant))->resolve(),
            'Tenant updated successfully.',
        );
    }

    /**
     * DELETE /tenants/{id}
     *
     * Soft-delete a tenant.
     */
    public function destroy(string $id): JsonResponse
    {
        $command = new DeleteTenantCommand(
            tenantId: $id,
            performedBy: (string) (auth()->id() ?? 'system'),
        );

        $this->tenantService->deleteTenant($command);

        return $this->noContent();
    }
}
