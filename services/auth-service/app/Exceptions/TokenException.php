<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class TokenException extends RuntimeException
{
    public function __construct(
        string $message = 'Token operation failed',
        int $code = 401,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
