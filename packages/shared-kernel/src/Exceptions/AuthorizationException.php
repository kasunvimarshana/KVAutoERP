<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Exceptions;

/**
 * Thrown when an authenticated principal attempts an action they are
 * not permitted to perform (RBAC/ABAC policy violation).
 *
 * Maps to HTTP 403 Forbidden in API response handlers.
 */
class AuthorizationException extends DomainException
{
    /** Default HTTP status code for this exception type. */
    public const HTTP_STATUS = 403;

    /**
     * @param  string                 $message    Human-readable denial reason (safe to surface in responses).
     * @param  array<string, mixed>   $context    Diagnostic context: action attempted, required permission, etc.
     * @param  \Throwable|null        $previous   The originating exception, if any.
     */
    public function __construct(
        string $message = 'You are not authorised to perform this action.',
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $context, self::HTTP_STATUS, $previous);
    }

    /**
     * Create an AuthorizationException for a specific missing permission.
     *
     * @param  string  $permission  The required permission slug.
     * @param  string  $action      Human-readable description of the attempted action.
     * @return self
     */
    public static function forPermission(string $permission, string $action = ''): self
    {
        $message = $action !== ''
            ? sprintf('Permission "%s" is required to %s.', $permission, $action)
            : sprintf('Permission "%s" is required for this action.', $permission);

        return new self($message, ['required_permission' => $permission, 'action' => $action]);
    }

    /**
     * Create an AuthorizationException for a missing role.
     *
     * @param  string  $role    The required role slug.
     * @param  string  $action  Human-readable description of the attempted action.
     * @return self
     */
    public static function forRole(string $role, string $action = ''): self
    {
        $message = $action !== ''
            ? sprintf('Role "%s" is required to %s.', $role, $action)
            : sprintf('Role "%s" is required for this action.', $role);

        return new self($message, ['required_role' => $role, 'action' => $action]);
    }
}
