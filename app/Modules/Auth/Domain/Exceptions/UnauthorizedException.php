<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Exceptions;

use RuntimeException;

class UnauthorizedException extends RuntimeException
{
    public function __construct(string $message = 'Unauthorized.')
    {
        parent::__construct($message, 403);
    }
}
