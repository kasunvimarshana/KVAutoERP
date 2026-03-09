<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Tenant\Commands\UpdateTenantConfigCommand;
use App\Application\Tenant\Queries\GetTenantConfigQuery;
use App\Http\Requests\UpdateTenantConfigRequest;
use App\Http\Resources\TenantConfigResource;
use App\Services\TenantService;
use App\Shared\Base\BaseController;
use Illuminate\Http\JsonResponse;

/**
 * Tenant Configuration Controller.
 *
 * Manages runtime configuration key/value pairs for individual tenants.
 */
final class TenantConfigController extends BaseController
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    /**
     * GET /tenants/{tenantId}/config
     *
     * Return all configuration entries for a tenant.
     */
    public function index(string $tenantId): JsonResponse
    {
        $query = new GetTenantConfigQuery(
            tenantId: $tenantId,
            configKey: null,
        );

        $configs = $this->tenantService->getTenantConfig($query);

        $data = array_map(
            fn (array $c) => (new TenantConfigResource((object) $c))->resolve(),
            $configs
        );

        return $this->success($data);
    }

    /**
     * GET /tenants/{tenantId}/config/{key}
     *
     * Return a single configuration entry for a tenant.
     */
    public function show(string $tenantId, string $key): JsonResponse
    {
        $query = new GetTenantConfigQuery(
            tenantId: $tenantId,
            configKey: $key,
        );

        $configs = $this->tenantService->getTenantConfig($query);

        if (empty($configs)) {
            return $this->notFound('Configuration key not found.');
        }

        return $this->success(
            (new TenantConfigResource((object) $configs[0]))->resolve()
        );
    }

    /**
     * POST /tenants/{tenantId}/config
     *
     * Create or update a configuration key for a tenant.
     */
    public function upsert(UpdateTenantConfigRequest $request, string $tenantId): JsonResponse
    {
        $command = new UpdateTenantConfigCommand(
            tenantId: $tenantId,
            configKey: $request->input('config_key'),
            configValue: $request->input('config_value'),
            environment: $request->input('environment', 'production'),
        );

        $this->tenantService->updateTenantConfig($command);

        return $this->success(null, 'Configuration updated successfully.');
    }
}
