<?php
namespace App\Exceptions;

use RuntimeException;

/**
 * Domain exception that carries an HTTP status code for controller-layer responses.
 */
class ServiceException extends RuntimeException
{
    public function __construct(string $message, private readonly int $httpStatus = 422)
    {
        parent::__construct($message);
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
