<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class RoleNotFoundException extends DomainException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct("Role not found: {$identifier}");
    }
}
