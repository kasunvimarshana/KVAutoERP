<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class TenantMiddleware { public function handle(Request $request, Closure $next): Response { $tenantId = $request->header('X-Tenant-ID'); if ($tenantId) { $request->attributes->set('tenant_id', $tenantId); } return $next($request); } }
