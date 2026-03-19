<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\TokenServiceContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the RS256 JWT on every protected request.
 * Downstream microservices use this middleware to validate tokens locally
 * without calling the Auth service.
 */
class VerifyJwtToken
{
    public function __construct(
        private readonly TokenServiceContract $tokenService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorized('Missing bearer token');
        }

        try {
            $claims = $this->tokenService->verify($token);

            // Attach decoded claims to the request for downstream use
            $request->attributes->set('jwt_claims', $claims);
            $request->attributes->set('user_id', $claims['sub'] ?? null);
            $request->attributes->set('tenant_id', $claims['tenant_id'] ?? null);
            $request->attributes->set('roles', $claims['roles'] ?? []);
            $request->attributes->set('permissions', $claims['permissions'] ?? []);
        } catch (\Throwable $e) {
            return $this->unauthorized($e->getMessage());
        }

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'meta'    => [],
            'errors'  => ['token' => $message],
            'message' => 'Unauthenticated',
        ], 401);
    }
}
