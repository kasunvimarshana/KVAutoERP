<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that the authenticated user has the required permission(s).
 * Usage: Route::middleware('require.permission:users.read,users.write')
 */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $userPermissions = (array) $request->attributes->get('permissions', []);

        foreach ($permissions as $permission) {
            if (! in_array($permission, $userPermissions, true)) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'meta'    => [],
                    'errors'  => ['permission' => "Missing required permission: {$permission}"],
                    'message' => 'Forbidden',
                ], 403);
            }
        }

        return $next($request);
    }
}
