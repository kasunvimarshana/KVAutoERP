<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedAuth\Contracts\TenantContextInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that the authenticated user has at least one of the required roles.
 * Reads roles from the JWT payload via TenantContext.
 *
 * Usage: ->middleware('require.role:admin,manager')
 */
class RequireRole
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $this->tenantContext->hasRole($roles)) {
            $required = implode(', ', $roles);
            return response()->json([
                'success' => false,
                'message' => "Role required: {$required}",
                'error'   => 'FORBIDDEN',
            ], 403);
        }

        return $next($request);
    }
}
