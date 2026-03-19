<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Exceptions;

/**
 * Thrown when a tenant isolation rule is violated.
 *
 * Covers scenarios such as:
 *   - An authenticated user attempting to access data belonging to a different tenant.
 *   - A missing or invalid tenant context on a request that requires one.
 *   - Cross-tenant data leakage detected at the repository layer.
 *
 * Maps to HTTP 403 Forbidden in API response handlers (not 404, to avoid
 * confirming the existence of another tenant's resources).
 */
class TenantException extends DomainException
{
    /** Default HTTP status code for this exception type. */
    public const HTTP_STATUS = 403;

    /**
     * @param  string                 $message   Human-readable description of the violation.
     * @param  array<string, mixed>   $context   Diagnostic context: expected/actual tenant IDs, etc.
     * @param  \Throwable|null        $previous  The originating exception, if any.
     */
    public function __construct(
        string $message = 'Tenant isolation violation detected.',
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $context, self::HTTP_STATUS, $previous);
    }

    /**
     * Create an exception for a missing tenant context on a request.
     *
     * @return self
     */
    public static function missingContext(): self
    {
        return new self(
            'No tenant context is present on the current request.',
            ['reason' => 'missing_tenant_context'],
        );
    }

    /**
     * Create an exception for an attempt to access a different tenant's resource.
     *
     * @param  string  $requestedTenantId  The tenant ID that was requested.
     * @param  string  $authorizedTenantId The tenant ID the principal is authorised for.
     * @return self
     */
    public static function accessDenied(string $requestedTenantId, string $authorizedTenantId): self
    {
        return new self(
            'Access to the requested tenant resource is denied.',
            [
                'reason'               => 'cross_tenant_access',
                'requested_tenant_id'  => $requestedTenantId,
                'authorized_tenant_id' => $authorizedTenantId,
            ],
        );
    }

    /**
     * Create an exception for an invalid or unrecognised tenant identifier.
     *
     * @param  string  $tenantId  The unrecognised tenant identifier.
     * @return self
     */
    public static function invalidTenant(string $tenantId): self
    {
        return new self(
            sprintf('Tenant "%s" does not exist or is not active.', $tenantId),
            ['reason' => 'invalid_tenant', 'tenant_id' => $tenantId],
        );
    }
}
