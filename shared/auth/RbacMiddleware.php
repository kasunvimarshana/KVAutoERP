<?php

declare(strict_types=1);

namespace App\Shared\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC (Role-Based Access Control) Middleware.
 *
 * Checks whether the authenticated user holds at least one of the roles
 * required by the route.  Roles are supplied as middleware parameters:
 *
 *   Route::get('/admin', ...)->middleware('rbac:admin');
 *   Route::get('/reports', ...)->middleware('rbac:admin,manager');
 *
 * The middleware integrates with both:
 *  - Spatie Permission package (preferred): User model uses HasRoles trait,
 *    and $user->hasRole() / $user->hasAnyRole() are available.
 *  - Simple roles: falls back to checking a `roles` relationship or attribute.
 *
 * Registration in Kernel.php:
 *   protected $routeMiddleware = [
 *       'rbac' => \App\Shared\Auth\RbacMiddleware::class,
 *   ];
 */
final class RbacMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request                       $request
     * @param  Closure(Request): Response    $next
     * @param  string                        ...$roles  One or more required roles.
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->unauthorizedResponse($request, 'Authentication required.');
        }

        if (empty($roles)) {
            // No specific roles required; being authenticated is sufficient.
            return $next($request);
        }

        if (!$this->userHasAnyRole($user, $roles)) {
            return $this->forbiddenResponse($request, 'Insufficient role permissions.', $roles);
        }

        return $next($request);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Role resolution
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return true if the user holds at least one of the required roles.
     *
     * Supports Spatie Permission, custom hasRole(), and a roles array attribute.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array<string>                               $roles
     * @return bool
     */
    private function userHasAnyRole(
        \Illuminate\Contracts\Auth\Authenticatable $user,
        array $roles,
    ): bool {
        // 1. Spatie Laravel Permission package
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        // 2. Custom hasRole() method
        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        // 3. Roles as a simple array attribute or relationship
        $userRoles = [];

        if (isset($user->roles)) {
            $r = $user->roles;
            if ($r instanceof \Illuminate\Support\Collection) {
                $userRoles = $r->pluck('name')->toArray();
            } elseif (is_array($r)) {
                $userRoles = $r;
            }
        }

        return !empty(array_intersect($roles, $userRoles));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Response helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function unauthorizedResponse(Request $request, string $message): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            data: [
                'success' => false,
                'message' => $message,
                'data'    => null,
                'meta'    => ['request_id' => $request->header('X-Request-ID')],
                'errors'  => [],
            ],
            status: 401,
        );
    }

    private function forbiddenResponse(
        Request $request,
        string $message,
        array $requiredRoles = [],
    ): \Illuminate\Http\JsonResponse {
        return response()->json(
            data: [
                'success' => false,
                'message' => $message,
                'data'    => null,
                'meta'    => [
                    'request_id'     => $request->header('X-Request-ID'),
                    'required_roles' => $requiredRoles,
                ],
                'errors'  => [],
            ],
            status: 403,
        );
    }
}
