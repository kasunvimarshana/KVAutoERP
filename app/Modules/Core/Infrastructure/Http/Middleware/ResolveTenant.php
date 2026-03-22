<?php

namespace Modules\Core\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->header('X-Tenant-ID') ?? optional($request->user())->tenant_id;
        if (!$tenantId) {
            throw new BadRequestHttpException('Tenant ID is required.');
        }

        $request->merge(['tenant_id' => $tenantId]);
        app()->instance('current_tenant_id', $tenantId);

        return $next($request);
    }
}
