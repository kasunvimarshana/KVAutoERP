<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use RuntimeException;

class UserAlreadyExistsException extends RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct("User with email '{$email}' already exists.", 409);
    }
}
