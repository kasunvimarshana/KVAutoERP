<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

/**
 * Domain-level Authentication Exception.
 * Thrown when authentication fails for business-logic reasons.
 */
class AuthenticationException extends Exception
{
    public function __construct(string $message = 'Authentication failed', int $code = 401)
    {
        parent::__construct($message, $code);
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials provided.', 401);
    }

    public static function accountInactive(): self
    {
        return new self('Account is not active. Please contact support.', 403);
    }

    public static function tokenExpired(): self
    {
        return new self('Token has expired.', 401);
    }

    public static function tenantMismatch(): self
    {
        return new self('Token does not belong to the current tenant.', 403);
    }
}
