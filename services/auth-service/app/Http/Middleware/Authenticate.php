<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Passport Token Authentication Middleware.
 *
 * Guards routes by verifying the Bearer token issued by Laravel Passport.
 * Sets the authenticated user on the request and rejects unauthenticated
 * requests with a 401 JSON response.
 */
final class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guard = empty($guards) ? 'api' : $guards[0];

        if (auth($guard)->guest()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(
                    data: [
                        'success' => false,
                        'message' => 'Unauthenticated.',
                        'data'    => null,
                        'meta'    => ['request_id' => $request->header('X-Request-ID')],
                        'errors'  => [],
                    ],
                    status: 401,
                );
            }

            throw new AuthenticationException(
                'Unauthenticated.',
                [$guard],
                null,
            );
        }

        // Bind the authenticated user so downstream middleware/controllers
        // can access it without re-resolving from the guard.
        $request->setUserResolver(fn () => auth($guard)->user());

        return $next($request);
    }
}
