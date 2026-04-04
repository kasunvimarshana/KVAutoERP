<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class PermissionNotFoundException extends DomainException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct("Permission not found: {$identifier}");
    }
}
