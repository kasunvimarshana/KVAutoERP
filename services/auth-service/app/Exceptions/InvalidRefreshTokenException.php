<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a refresh token is invalid, expired, or already revoked.
 */
final class InvalidRefreshTokenException extends RuntimeException
{
    public function __construct(string $message = 'Refresh token is invalid or expired.')
    {
        parent::__construct($message, 401);
    }
}
