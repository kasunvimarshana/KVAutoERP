<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC role enforcement middleware.
 *
 * Verifies that the authenticated user's JWT claims contain at least one
 * of the required role slugs. Must run after VerifyJwtMiddleware (which
 * attaches `jwt_claims` to the request attributes).
 *
 * Usage (route definition):
 *   ->middleware('require.role:admin')
 *   ->middleware('require.role:admin,warehouse-manager')
 *
 * Multiple slugs are separated by commas. ANY listed role is sufficient
 * (OR semantics): the request is allowed if the user holds at least one
 * of the specified roles. To require ALL roles, chain separate middleware.
 *
 * Returns 403 Forbidden when:
 *   - No `jwt_claims` attribute is present on the request.
 *   - The `roles` claim is missing or empty.
 *   - None of the required role slugs are present in the claim list.
 */
final class RequireRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request                         $request      The HTTP request.
     * @param  Closure(Request): Response      $next         The next middleware/controller.
     * @param  string                          ...$required  One or more acceptable role slugs (OR logic).
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$required): Response
    {
        /** @var array<string, mixed>|null $claims */
        $claims = $request->attributes->get('jwt_claims');

        if ($claims === null) {
            return ApiResponse::forbidden('Authentication context is missing. Ensure JWT middleware runs first.');
        }

        /** @var array<int, string> $userRoles */
        $userRoles = (array) ($claims['roles'] ?? []);

        foreach ($required as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        return ApiResponse::forbidden(
            sprintf('You do not have the required role. Expected one of: %s', implode(', ', $required)),
        );
    }
}
