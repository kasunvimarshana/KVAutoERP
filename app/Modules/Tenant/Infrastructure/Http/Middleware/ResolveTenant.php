<?php

namespace Modules\Tenant\Infrastructure\Http\Middleware;

use Closure;
use Modules\Tenant\Infrastructure\Services\TenantConfigClient;
use Modules\Tenant\Application\Services\TenantConfigManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ResolveTenant
{
    public function __construct(
        protected TenantConfigClient $client,
        protected TenantConfigManager $manager
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Resolve tenant ID
        $tenantId = $request->header('X-Tenant-ID') ?? optional($request->user())->tenant_id;
        if (!$tenantId) {
            throw new BadRequestHttpException('Tenant ID is required.');
        }

        // Validate that the tenant ID is a positive integer to prevent injection attacks
        $tenantId = filter_var($tenantId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($tenantId === false) {
            throw new BadRequestHttpException('Tenant ID must be a valid positive integer.');
        }

        // Attach tenant ID to request for later use
        $request->merge(['tenant_id' => $tenantId]);
        app()->instance('current_tenant_id', $tenantId);

        // Fetch tenant configuration (cached)
        $config = $this->client->getConfig($tenantId);
        if (!$config) {
            throw new BadRequestHttpException('Invalid tenant ID.');
        }

        // Apply configuration to Laravel
        $this->manager->apply($config);

        // Bind tenant config to container for later access
        app()->instance('tenant.config', $config);

        return $next($request);
    }
}
