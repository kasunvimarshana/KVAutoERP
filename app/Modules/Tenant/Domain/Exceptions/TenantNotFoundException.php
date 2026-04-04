<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class TenantNotFoundException extends DomainException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct("Tenant not found: {$identifier}");
    }
}
