<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command DTO for authenticating a user.
 */
final readonly class LoginCommand
{
    public function __construct(
        public string $tenantId,
        public string $email,
        public string $password,
        public array $deviceInfo = [],
        public string $ipAddress = '',
        public bool $remember = false,
    ) {}
}
