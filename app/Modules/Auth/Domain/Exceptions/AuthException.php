<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class AuthException extends DomainException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials provided.');
    }

    public static function userInactive(): self
    {
        return new self('User account is inactive.');
    }

    public static function unauthorized(): self
    {
        return new self('Unauthorized action.');
    }
}
