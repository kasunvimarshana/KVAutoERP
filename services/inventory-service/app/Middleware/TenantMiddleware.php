<?php
namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant identifier is required.',
            ], 400);
        }

        app()->instance('tenant.id', $tenantId);
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }

    private function resolveTenantId(Request $request): ?string
    {
        $header = $request->header('X-Tenant-ID');
        if (!empty($header)) {
            return $header;
        }

        $query = $request->query('tenant_id');
        if (!empty($query)) {
            return $query;
        }

        $host  = $request->getHost();
        $parts = explode('.', $host);
        if (count($parts) >= 3 && $parts[0] !== 'www' && $parts[0] !== 'api') {
            return $parts[0];
        }

        $body = $request->input('tenant_id');
        if (!empty($body)) {
            return $body;
        }

        return null;
    }
}
