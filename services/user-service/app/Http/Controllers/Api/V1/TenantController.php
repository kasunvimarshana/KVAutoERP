<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TenantServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTenantRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TenantServiceContract $tenantService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);
        $perPage = (int) $request->get('per_page', 20);

        $result = $this->tenantService->list($filters, $perPage);

        return $this->paginatedResponse($result['data'], $result['pagination']);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        return $this->successResponse($tenant);
    }

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->create($request->validated());

        return $this->successResponse($tenant, 'Tenant created successfully', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data   = $request->validate([
            'name'          => ['sometimes', 'string', 'max:255'],
            'status'        => ['sometimes', 'in:active,inactive,suspended'],
            'iam_provider'  => ['sometimes', 'string', 'in:local,okta,keycloak,azure_ad,oauth2,saml'],
            'configuration' => ['sometimes', 'array'],
        ]);

        $tenant = $this->tenantService->update($id, $data);

        return $this->successResponse($tenant, 'Tenant updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->tenantService->delete($id);

        return $this->successResponse(null, 'Tenant deleted successfully');
    }

    public function hierarchy(string $id): JsonResponse
    {
        $hierarchy = $this->tenantService->getHierarchy($id);

        return $this->successResponse($hierarchy);
    }

    /**
     * Get the IAM provider configuration for a tenant.
     *
     * GET /api/v1/tenants/{id}/iam-config
     */
    public function getIamConfig(string $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        $config = (array) ($tenant['configuration'] ?? []);

        return $this->successResponse([
            'tenant_id'    => $tenant['id'],
            'iam_provider' => $tenant['iam_provider'] ?? 'local',
            'iam_config'   => $config['iam'] ?? [],
        ]);
    }

    /**
     * Update the IAM provider and its runtime configuration for a tenant.
     *
     * PUT /api/v1/tenants/{id}/iam-config
     *
     * Body: { "iam_provider": "okta", "iam_config": { "domain": "...", ... } }
     */
    public function updateIamConfig(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'iam_provider' => ['required', 'string', 'in:local,okta,keycloak,azure_ad,oauth2,saml'],
            'iam_config'   => ['required', 'array'],
        ]);

        $tenant = $this->tenantService->findById($id);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        // Merge new IAM config into existing configuration JSON
        $config        = (array) ($tenant['configuration'] ?? []);
        $config['iam'] = $data['iam_config'];

        $updated = $this->tenantService->update($id, [
            'iam_provider'  => $data['iam_provider'],
            'configuration' => $config,
        ]);

        return $this->successResponse([
            'tenant_id'    => $updated['id'],
            'iam_provider' => $updated['iam_provider'],
            'iam_config'   => $config['iam'],
        ], 'IAM configuration updated successfully');
    }

    /**
     * Get feature flags for a tenant.
     *
     * GET /api/v1/tenants/{id}/feature-flags
     */
    public function getFeatureFlags(string $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        $config       = (array) ($tenant['configuration'] ?? []);
        $featureFlags = (array) ($config['feature_flags'] ?? []);

        return $this->successResponse([
            'tenant_id'     => $tenant['id'],
            'feature_flags' => $featureFlags,
        ]);
    }

    /**
     * Update feature flags for a tenant at runtime (no redeployment required).
     *
     * PUT /api/v1/tenants/{id}/feature-flags
     * Body: { "feature_flags": { "new_dashboard": true, "beta_api": false } }
     */
    public function updateFeatureFlags(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'feature_flags' => ['required', 'array'],
        ]);

        $tenant = $this->tenantService->findById($id);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        $config                  = (array) ($tenant['configuration'] ?? []);
        $config['feature_flags'] = $data['feature_flags'];

        $updated = $this->tenantService->update($id, ['configuration' => $config]);

        return $this->successResponse([
            'tenant_id'     => $updated['id'],
            'feature_flags' => $config['feature_flags'],
        ], 'Feature flags updated successfully');
    }
}
