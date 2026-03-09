<?php

declare(strict_types=1);

namespace App\Shared\Tenant;

use App\Shared\Contracts\TenantInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Resolution Middleware.
 *
 * Resolves the active tenant from the incoming HTTP request and calls
 * {@see TenantManager::switchTenant()} to configure all runtime settings.
 *
 * Resolution order (configurable via TENANT_IDENTIFICATION_DRIVER env):
 *  1. X-Tenant-ID header
 *  2. Sub-domain
 *  3. JWT 'tid' claim
 *
 * Register in app/Http/Kernel.php:
 *   protected $middlewareGroups = [
 *       'api' => [
 *           \App\Shared\Tenant\TenantMiddleware::class,
 *           ...
 *       ],
 *   ];
 */
final class TenantMiddleware
{
    public function __construct(
        private readonly TenantManager $tenantManager,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @param  string   ...$options  Optional middleware parameters:
     *                               'optional' – do not abort on missing tenant.
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$options): Response
    {
        $optional = in_array('optional', $options, strict: true);

        $tenantId = $this->resolveTenantId($request);

        if ($tenantId === null) {
            if ($optional) {
                return $next($request);
            }

            return response()->json(
                data: [
                    'success' => false,
                    'message' => 'Tenant identification is required.',
                    'data'    => null,
                    'meta'    => ['request_id' => $request->header('X-Request-ID')],
                    'errors'  => [],
                ],
                status: 401,
            );
        }

        try {
            $this->tenantManager->switchTenant($tenantId);
        } catch (\RuntimeException $e) {
            return response()->json(
                data: [
                    'success' => false,
                    'message' => "Tenant not found: {$tenantId}",
                    'data'    => null,
                    'meta'    => ['request_id' => $request->header('X-Request-ID')],
                    'errors'  => [],
                ],
                status: 404,
            );
        }

        // Attach resolved tenant ID to request attributes for downstream use.
        $request->attributes->set('tenant_id', $tenantId);
        $request->attributes->set('tenant', $this->tenantManager->getTenantSettings());

        return $next($request);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Delegate tenant ID resolution to TenantManager based on the configured driver.
     *
     * @param  Request  $request
     * @return string|null
     */
    private function resolveTenantId(Request $request): ?string
    {
        $driver = config('tenant.identification_driver', env('TENANT_IDENTIFICATION_DRIVER', 'header'));

        return match ($driver) {
            'header'     => $this->fromHeader($request),
            'subdomain'  => $this->fromSubdomain($request),
            'jwt_claim'  => $this->fromJwt($request),
            'path'       => $this->fromPath($request),
            default      => $this->tenantManager->resolveFromRequest($request),
        };
    }

    private function fromHeader(Request $request): ?string
    {
        $headerName = config('tenant.header_name', env('TENANT_HEADER_NAME', 'X-Tenant-ID'));

        return $request->header($headerName) ?: null;
    }

    private function fromSubdomain(Request $request): ?string
    {
        $appDomain = config('app.domain', parse_url(config('app.url', ''), PHP_URL_HOST) ?? '');
        $host      = $request->getHost();

        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
            return (!empty($subdomain) && $subdomain !== 'www') ? $subdomain : null;
        }

        return null;
    }

    private function fromJwt(Request $request): ?string
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );

            return $payload['tid'] ?? $payload['tenant_id'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function fromPath(Request $request): ?string
    {
        // Expects routes like /api/{tenant}/resources
        return $request->route('tenant') ?: $request->segment(2);
    }
}
