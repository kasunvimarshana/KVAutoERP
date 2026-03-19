<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC permission enforcement middleware.
 *
 * Verifies that the authenticated user's JWT claims contain all of the
 * required permission slugs. Must run after VerifyJwtMiddleware (which
 * attaches `jwt_claims` to the request attributes).
 *
 * Usage (route definition):
 *   ->middleware('require.permission:products.manage')
 *   ->middleware('require.permission:inventory.receive,inventory.dispatch')
 *
 * Multiple slugs are separated by commas. ALL listed permissions must be
 * present (AND semantics). To require ANY of a set of permissions, chain
 * separate middleware instances.
 *
 * Returns 403 Forbidden when:
 *   - No `jwt_claims` attribute is present on the request.
 *   - The `permissions` claim is missing or empty.
 *   - Any required permission slug is absent from the claim list.
 */
final class RequirePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request                         $request      The HTTP request.
     * @param  Closure(Request): Response      $next         The next middleware/controller.
     * @param  string                          ...$required  One or more required permission slugs.
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$required): Response
    {
        /** @var array<string, mixed>|null $claims */
        $claims = $request->attributes->get('jwt_claims');

        if ($claims === null) {
            return ApiResponse::forbidden('Authentication context is missing. Ensure JWT middleware runs first.');
        }

        /** @var array<int, string> $userPermissions */
        $userPermissions = (array) ($claims['permissions'] ?? []);

        foreach ($required as $permission) {
            if (!in_array($permission, $userPermissions, true)) {
                return ApiResponse::forbidden(
                    sprintf('You do not have the required permission: %s', $permission),
                );
            }
        }

        return $next($request);
    }
}
