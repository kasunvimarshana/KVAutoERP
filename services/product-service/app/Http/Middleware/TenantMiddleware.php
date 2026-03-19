<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Shared\Core\MultiTenancy\TenantManager;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * @var TenantManager
     */
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        // In a real production system, you would fetch tenant config from a central database or cache
        // For this demonstration, we'll use a mock configuration
        $config = $this->getTenantConfig($tenantId);

        if (!$config) {
            return response()->json(['error' => 'Invalid Tenant ID'], 404);
        }

        $this->tenantManager->setTenant($tenantId, $config);

        return $next($request);
    }

    /**
     * Mock function to get tenant configuration
     *
     * @param string $tenantId
     * @return array|null
     */
    protected function getTenantConfig(string $tenantId): ?array
    {
        // Mocking two tenants
        $tenants = [
            'tenant_1' => [
                'database' => [
                    'database' => 'tenant_1_db',
                    'username' => 'root',
                    'password' => '',
                ],
                'mail' => [
                    'host' => 'smtp.tenant1.com',
                ],
                'feature_flags' => [
                    'pharma_compliance' => false,
                ]
            ],
            'tenant_2' => [
                'database' => [
                    'database' => 'tenant_2_db',
                    'username' => 'root',
                    'password' => '',
                ],
                'mail' => [
                    'host' => 'smtp.tenant2.com',
                ],
                'feature_flags' => [
                    'pharma_compliance' => true,
                ]
            ],
        ];

        return $tenants[$tenantId] ?? null;
    }
}
