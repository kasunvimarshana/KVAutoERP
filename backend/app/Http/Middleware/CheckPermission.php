<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $authUser = $request->get('auth_user');

        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userRoles  = $authUser->realm_access->roles ?? [];
        $userScopes = explode(' ', $authUser->scope ?? '');

        foreach ($permissions as $permission) {
            if (in_array($permission, $userRoles, true) || in_array($permission, $userScopes, true)) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Forbidden - Insufficient permissions'], 403);
    }
}
