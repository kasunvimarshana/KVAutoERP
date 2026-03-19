<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\Services\PermissionServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that the authenticated user has the required permission.
 * Usage: ->middleware('require.permission:inventory.view')
 */
class RequirePermission
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
    ) {}

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $payload = $request->attributes->get('jwt_payload', []);
        $userId = $payload['user_id'] ?? $payload['sub'] ?? null;
        $tenantId = $payload['tenant_id'] ?? null;

        if ($userId === null || $tenantId === null) {
            return $this->forbidden('Authentication context missing.');
        }

        foreach ($permissions as $permission) {
            if (! $this->permissionService->hasPermission($userId, $permission, $tenantId)) {
                return $this->forbidden("Permission denied: {$permission}");
            }
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
