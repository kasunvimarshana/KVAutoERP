<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $requestTenantId = $this->resolveRequestTenantId($request);

        if ($requestTenantId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant context is missing.',
            ], 400);
        }

        if ($user->tenant_id !== $requestTenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Token tenant does not match the request tenant.',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'User account is inactive.',
            ], 403);
        }

        return $next($request);
    }

    private function resolveRequestTenantId(Request $request): ?string
    {
        // Priority: app container binding set by TenantMiddleware > header > body
        if (app()->has('tenant.id')) {
            return app('tenant.id');
        }

        $header = $request->header('X-Tenant-ID');
        if (!empty($header)) {
            return $header;
        }

        $body = $request->input('tenant_id');
        if (!empty($body)) {
            return $body;
        }

        return null;
    }
}
