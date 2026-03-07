<?php

namespace App\Http\Middleware;

use App\Core\Authorization\PolicyManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that performs ABAC authorisation.
 *
 * Usage in routes:  ->middleware('abac:product.view')
 */
class AbacMiddleware
{
    public function __construct(protected PolicyManager $policyManager) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$this->policyManager->authorize($user, $action)) {
            return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
        }

        return $next($request);
    }
}
