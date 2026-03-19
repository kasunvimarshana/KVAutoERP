<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class AuthException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
