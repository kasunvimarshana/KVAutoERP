<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use RuntimeException;

class InvalidPasswordException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The provided password is incorrect.', 422);
    }
}
