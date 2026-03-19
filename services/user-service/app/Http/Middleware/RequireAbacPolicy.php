<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\PolicyServiceContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Evaluates ABAC policies for the current request.
 *
 * Usage in route definitions:
 *   ->middleware('abac:users:delete')
 *
 * The action string is passed as the middleware parameter and matched
 * against the active policies for the authenticated tenant.
 */
class RequireAbacPolicy
{
    public function __construct(
        private readonly PolicyServiceContract $policyService,
    ) {}

    public function handle(Request $request, Closure $next, string $action = '*'): Response
    {
        $claims = (array) $request->attributes->get('jwt_claims', []);

        if (empty($claims)) {
            return $this->forbidden('No authentication claims found');
        }

        $resource = [
            'entity_type' => $request->route()?->getName() ?? '',
            'tenant_id'   => $claims['tenant_id'] ?? '',
        ];

        $environment = [
            'ip_address' => $request->ip(),
            'method'     => $request->method(),
        ];

        $allowed = $this->policyService->evaluate(
            subject:     $claims,
            action:      $action,
            resource:    $resource,
            environment: $environment,
        );

        if (! $allowed) {
            return $this->forbidden("Policy denied action: {$action}");
        }

        return $next($request);
    }

    private function forbidden(string $message): Response
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'meta'    => [],
            'errors'  => ['policy' => $message],
            'message' => 'Forbidden',
        ], 403);
    }
}
