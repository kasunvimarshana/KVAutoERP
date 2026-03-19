<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a JWT token is invalid, malformed, or expired.
 */
final class InvalidTokenException extends RuntimeException
{
    public function __construct(string $message = 'Token is invalid or expired.')
    {
        parent::__construct($message, 401);
    }
}
