<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when authentication credentials are invalid.
 */
final class AuthenticationException extends RuntimeException
{
    /**
     * @param  string  $message
     */
    public function __construct(string $message = 'Invalid credentials.')
    {
        parent::__construct($message, 401);
    }
}
