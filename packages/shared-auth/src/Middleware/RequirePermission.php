<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedAuth\Contracts\TenantContextInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that the authenticated user has the required permission.
 * Reads permissions from the JWT payload via TenantContext.
 *
 * Usage: ->middleware('require.permission:inventory.view')
 */
class RequirePermission
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        foreach ($permissions as $permission) {
            if (! $this->tenantContext->hasPermission($permission)) {
                return response()->json([
                    'success' => false,
                    'message' => "Permission denied: {$permission}",
                    'error'   => 'FORBIDDEN',
                ], 403);
            }
        }

        return $next($request);
    }
}
