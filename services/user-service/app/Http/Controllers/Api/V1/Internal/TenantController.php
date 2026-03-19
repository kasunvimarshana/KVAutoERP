<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Internal;

use App\Contracts\TenantServiceContract;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal API consumed exclusively by the Auth service.
 * All routes here are protected by VerifyServiceToken middleware.
 *
 * Exposes tenant IAM configuration so the Auth service can dynamically
 * resolve the correct identity provider per tenant at login time without
 * relying on static environment variables.
 */
class TenantController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TenantServiceContract $tenantService,
    ) {}

    /**
     * Return the IAM provider name and its runtime configuration for a tenant.
     *
     * GET /api/v1/internal/tenants/{tenantId}/iam-config
     */
    public function getIamConfig(string $tenantId): JsonResponse
    {
        $tenant = $this->tenantService->findById($tenantId);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        $config = (array) ($tenant['configuration'] ?? []);

        return $this->successResponse([
            'tenant_id'    => $tenant['id'],
            'iam_provider' => $tenant['iam_provider'] ?? 'local',
            'iam_config'   => $config['iam'] ?? [],
            'status'       => $tenant['status'],
        ]);
    }

    /**
     * Return feature flags for a tenant.
     *
     * GET /api/v1/internal/tenants/{tenantId}/feature-flags
     */
    public function getFeatureFlags(string $tenantId): JsonResponse
    {
        $tenant = $this->tenantService->findById($tenantId);

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
     * Update feature flags for a tenant (called by auth-service for dynamic config propagation).
     *
     * PUT /api/v1/internal/tenants/{tenantId}/feature-flags
     */
    public function updateFeatureFlags(Request $request, string $tenantId): JsonResponse
    {
        $request->validate([
            'feature_flags' => ['required', 'array'],
        ]);

        $tenant = $this->tenantService->findById($tenantId);

        if (! $tenant) {
            return $this->errorResponse('Tenant not found', [], 404);
        }

        $config                 = (array) ($tenant['configuration'] ?? []);
        $config['feature_flags'] = $request->input('feature_flags');

        $updated = $this->tenantService->update($tenantId, ['configuration' => $config]);

        return $this->successResponse([
            'tenant_id'     => $updated['id'],
            'feature_flags' => $config['feature_flags'],
        ], 'Feature flags updated');
    }
}
