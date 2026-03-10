<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

class UserNotFoundException extends RuntimeException
{
    public function __construct(int|string $id)
    {
        parent::__construct("User profile for user ID {$id} not found.", 404);
    }
}
