<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Ability Middleware (ABAC - Attribute-Based Access Control)
 * 
 * Validates that the authenticated user's token has the required scope/ability.
 * Implements fine-grained ABAC on top of Passport scopes.
 */
class CheckAbility
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Super admin bypasses all ability checks
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $token = $user->token();

        foreach ($abilities as $ability) {
            if (!$token || !$token->can($ability)) {
                return response()->json([
                    'success' => false,
                    'message' => "Missing required ability: {$ability}",
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }
        }

        return $next($request);
    }
}
