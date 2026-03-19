<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use KvEnterprise\SharedKernel\Exceptions\TenantException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use KvEnterprise\SharedKernel\ValueObjects\TenantId;

/**
 * Middleware that resolves and binds the tenant context for every request.
 *
 * Resolution order:
 *   1. `X-Tenant-ID` HTTP header (service-to-service / API gateway).
 *   2. `tenant_id` claim from the verified JWT token (set by auth middleware).
 *   3. Tenant subdomain (e.g. "acme.saas.example.com" → "acme").
 *
 * If no tenant can be resolved the request is rejected with 400 Bad Request.
 * If the resolved tenant ID is structurally invalid (not a UUID v4) the
 * request is rejected with 400 Bad Request.
 *
 * The resolved TenantId is:
 *   - Stored on the Request object as the `_tenant_id` attribute.
 *   - Bound into the service container under the `TenantId::class` abstract
 *     so that repositories and services can resolve it via dependency injection.
 */
final class TenantContextMiddleware
{
    /** Header name used by API gateways and service-to-service calls. */
    private const TENANT_HEADER = 'X-Tenant-ID';

    /** JWT claim name that carries the tenant identifier. */
    private const JWT_CLAIM_TENANT = 'tenant_id';

    /** Request attribute key under which the TenantId is stored. */
    private const REQUEST_ATTRIBUTE = '_tenant_id';

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request  The current HTTP request.
     * @param  Closure(Request): mixed  $next  The next middleware / controller.
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $tenantId = $this->resolveTenantId($request);
        } catch (TenantException $e) {
            return ApiResponse::error($e->getMessage(), [], TenantException::HTTP_STATUS);
        }

        // Attach the TenantId to the request for downstream use.
        $request->attributes->set(self::REQUEST_ATTRIBUTE, $tenantId);

        // Bind into the service container so repositories can inject it.
        app()->instance(TenantId::class, $tenantId);

        return $next($request);
    }

    /**
     * Attempt to resolve the tenant identifier from the request.
     *
     * @param  Request  $request
     * @return TenantId
     *
     * @throws TenantException When no tenant context can be determined.
     */
    private function resolveTenantId(Request $request): TenantId
    {
        $raw = $this->extractRawTenantId($request);

        if ($raw === null || $raw === '') {
            throw TenantException::missingContext();
        }

        try {
            return TenantId::fromString($raw);
        } catch (\InvalidArgumentException) {
            throw new TenantException(
                sprintf('The tenant identifier "%s" is not a valid UUID.', $raw),
                ['reason' => 'invalid_tenant_uuid', 'raw' => $raw],
            );
        }
    }

    /**
     * Extract the raw tenant ID string using the resolution chain.
     *
     * @param  Request  $request
     * @return string|null
     */
    private function extractRawTenantId(Request $request): ?string
    {
        // 1. Explicit header (API gateway / service mesh).
        if ($request->hasHeader(self::TENANT_HEADER)) {
            return $request->header(self::TENANT_HEADER);
        }

        // 2. JWT claim (set by the auth middleware that ran before this one).
        $jwtClaims = $request->attributes->get('jwt_claims', []);
        if (is_array($jwtClaims) && isset($jwtClaims[self::JWT_CLAIM_TENANT])) {
            return (string) $jwtClaims[self::JWT_CLAIM_TENANT];
        }

        // 3. Authenticated user payload (Laravel Auth guard fallback).
        $user = $request->user();
        if ($user !== null && isset($user->{self::JWT_CLAIM_TENANT})) {
            return (string) $user->{self::JWT_CLAIM_TENANT};
        }

        return null;
    }
}
