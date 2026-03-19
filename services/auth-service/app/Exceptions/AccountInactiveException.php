<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a user account is disabled or inactive.
 */
final class AccountInactiveException extends RuntimeException
{
    public function __construct(string $message = 'Account is inactive.')
    {
        parent::__construct($message, 403);
    }
}
