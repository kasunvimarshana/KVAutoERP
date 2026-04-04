<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class UserNotFoundException extends DomainException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct("User not found: {$identifier}");
    }
}
