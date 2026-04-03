<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Exceptions;

class InvalidCredentialsException extends AuthenticationException {
    public function __construct(string $message = 'Invalid credentials provided') {
        parent::__construct($message);
    }
}
