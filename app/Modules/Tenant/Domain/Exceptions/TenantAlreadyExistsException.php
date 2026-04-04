<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Exceptions;

use RuntimeException;

class TenantAlreadyExistsException extends RuntimeException
{
    public function __construct(string $slug)
    {
        parent::__construct("Tenant with slug '{$slug}' already exists.", 409);
    }
}
