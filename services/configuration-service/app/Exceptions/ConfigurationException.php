<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ConfigurationException extends RuntimeException
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
