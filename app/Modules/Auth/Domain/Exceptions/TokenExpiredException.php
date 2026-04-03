<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\Exceptions;

class TokenExpiredException extends AuthenticationException {
    public function __construct(string $message = 'Token has expired') {
        parent::__construct($message);
    }
}
