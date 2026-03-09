<?php

declare(strict_types=1);

namespace App\Shared\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ABAC (Attribute-Based Access Control) Middleware.
 *
 * Evaluates a policy by comparing:
 *  - Subject attributes  – properties of the authenticated user.
 *  - Resource attributes – properties of the route/resource being accessed.
 *  - Action attributes   – HTTP method and route name.
 *  - Environment         – time-of-day, IP, tenant, etc.
 *
 * Policy names are supplied as middleware parameters:
 *
 *   Route::put('/products/{id}', ...)->middleware('abac:products.update');
 *   Route::get('/reports', ...)->middleware('abac:reports.view,audit.required');
 *
 * Registration in Kernel.php:
 *   protected $routeMiddleware = [
 *       'abac' => \App\Shared\Auth\AbacMiddleware::class,
 *   ];
 */
final class AbacMiddleware
{
    public function __construct(
        private readonly AbacPolicyEvaluator $evaluator,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request                     $request
     * @param  Closure(Request): Response  $next
     * @param  string                      ...$policies  Policy names to evaluate.
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$policies): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->jsonResponse(401, 'Authentication required.');
        }

        $subject     = $this->buildSubject($user, $request);
        $resource    = $this->buildResource($request);
        $action      = $this->buildAction($request);
        $environment = $this->buildEnvironment($request);

        foreach ($policies as $policy) {
            $allowed = $this->evaluator->evaluate(
                subject: $subject,
                resource: array_merge($resource, ['policy' => $policy]),
                action: $action,
                environment: $environment,
            );

            if (!$allowed) {
                return $this->jsonResponse(
                    403,
                    "Access denied by policy [{$policy}].",
                    ['policy' => $policy],
                    $request,
                );
            }
        }

        return $next($request);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Attribute builders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  Request                                     $request
     * @return array<string,mixed>
     */
    private function buildSubject(
        \Illuminate\Contracts\Auth\Authenticatable $user,
        Request $request,
    ): array {
        $roles       = [];
        $permissions = [];

        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->toArray();
        } elseif (isset($user->roles)) {
            $r     = $user->roles;
            $roles = $r instanceof \Illuminate\Support\Collection
                ? $r->pluck('name')->toArray()
                : (array) $r;
        }

        if (method_exists($user, 'getAllPermissions')) {
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        }

        return [
            'id'          => $user->getAuthIdentifier(),
            'roles'       => $roles,
            'permissions' => $permissions,
            'tenant_id'   => $request->header('X-Tenant-ID') ?? $request->attributes->get('tenant_id'),
            'attributes'  => method_exists($user, 'toArray') ? $user->toArray() : [],
        ];
    }

    /**
     * @param  Request  $request
     * @return array<string,mixed>
     */
    private function buildResource(Request $request): array
    {
        return [
            'route'      => $request->route()?->getName(),
            'path'       => $request->path(),
            'parameters' => $request->route()?->parameters() ?? [],
            'tenant_id'  => $request->header('X-Tenant-ID') ?? $request->attributes->get('tenant_id'),
        ];
    }

    /**
     * @param  Request  $request
     * @return array<string,mixed>
     */
    private function buildAction(Request $request): array
    {
        return [
            'method'     => strtoupper($request->method()),
            'route_name' => $request->route()?->getName(),
        ];
    }

    /**
     * @param  Request  $request
     * @return array<string,mixed>
     */
    private function buildEnvironment(Request $request): array
    {
        return [
            'ip'         => $request->ip(),
            'timestamp'  => now()->toIso8601String(),
            'hour'       => (int) now()->format('H'),
            'user_agent' => $request->userAgent(),
            'is_secure'  => $request->isSecure(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Response helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function jsonResponse(
        int $status,
        string $message,
        array $meta = [],
        ?Request $request = null,
    ): \Illuminate\Http\JsonResponse {
        return response()->json(
            data: [
                'success' => false,
                'message' => $message,
                'data'    => null,
                'meta'    => array_merge(
                    ['request_id' => $request?->header('X-Request-ID')],
                    $meta,
                ),
                'errors'  => [],
            ],
            status: $status,
        );
    }
}
