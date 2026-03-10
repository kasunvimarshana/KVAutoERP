<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

/**
 * Domain-level Tenant Exception.
 */
class TenantException extends Exception
{
    public static function notFound(string $tenantId): self
    {
        return new self("Tenant '{$tenantId}' not found.", 404);
    }

    public static function inactive(string $tenantId): self
    {
        return new self("Tenant '{$tenantId}' is inactive.", 403);
    }

    public static function configNotFound(string $key): self
    {
        return new self("Configuration key '{$key}' not found for current tenant.", 404);
    }
}
