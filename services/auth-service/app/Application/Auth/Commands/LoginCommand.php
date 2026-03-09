<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command: Log In.
 *
 * Carries the data required to authenticate a user.
 */
final readonly class LoginCommand
{
    /**
     * @param  string       $email      The user's e-mail address.
     * @param  string       $password   Plaintext password (never stored).
     * @param  string       $tenantId   Tenant scope for the authentication attempt.
     * @param  array|null   $deviceInfo Optional device metadata (user-agent, IP, etc.).
     */
    public function __construct(
        public string $email,
        public string $password,
        public string $tenantId,
        public ?array $deviceInfo = null,
    ) {}
}
