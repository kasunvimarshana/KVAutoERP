<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Contracts\Services\TenantConfigServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Tenant Configuration Controller
 * 
 * Manages dynamic tenant configurations at runtime.
 * Thin controller - delegates to TenantConfigService.
 */
class TenantController extends Controller
{
    public function __construct(
        private readonly TenantConfigServiceInterface $tenantConfigService,
    ) {}

    /**
     * GET /api/auth/tenants/{tenantId}/config
     * 
     * Get tenant configuration.
     */
    public function getConfig(string $tenantId): JsonResponse
    {
        $tenant = $this->tenantConfigService->getTenant($tenantId);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'plan' => $tenant->plan,
                'is_active' => $tenant->is_active,
                'feature_flags' => $tenant->feature_flags,
                'settings' => $tenant->settings,
            ],
        ]);
    }

    /**
     * PATCH /api/auth/tenants/{tenantId}/config
     * 
     * Update a tenant configuration at runtime (no restart required).
     */
    public function updateConfig(Request $request, string $tenantId): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $success = $this->tenantConfigService->set($tenantId, $request->input('key'), $request->input('value'));

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Configuration updated successfully. Changes are effective immediately.' : 'Failed to update configuration.',
        ]);
    }

    /**
     * PATCH /api/auth/tenants/{tenantId}/features
     * 
     * Toggle a feature flag at runtime.
     */
    public function toggleFeature(Request $request, string $tenantId): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $success = $this->tenantConfigService->setFeatureFlag(
            $tenantId,
            $request->input('feature'),
            $request->boolean('enabled')
        );

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "Feature '{$request->input('feature')}' " . ($request->boolean('enabled') ? 'enabled' : 'disabled') . '.'
                : 'Failed to update feature flag.',
        ]);
    }
}
