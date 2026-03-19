<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Services\PermissionServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that the authenticated user has at least one of the required roles.
 * Usage: ->middleware('require.role:admin,manager')
 */
class RequireRole
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $payload = $request->attributes->get('jwt_payload', []);
        $userId = $payload['user_id'] ?? $payload['sub'] ?? null;
        $tenantId = $payload['tenant_id'] ?? null;

        if ($userId === null || $tenantId === null) {
            return $this->forbidden('Authentication context missing.');
        }

        if (! $this->permissionService->hasRole($userId, $roles, $tenantId)) {
            $required = implode(', ', $roles);
            return $this->forbidden("Role required: {$required}");
        }

        return $next($request);
    }

    private function forbidden(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => 'FORBIDDEN',
        ], 403);
    }
}
