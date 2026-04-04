<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class UserEmailAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("A user with email '{$email}' already exists.");
    }
}
