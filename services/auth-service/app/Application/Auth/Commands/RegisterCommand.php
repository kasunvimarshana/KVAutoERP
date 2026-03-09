<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command: Register.
 *
 * Carries the data required to create a new user account.
 */
final readonly class RegisterCommand
{
    /**
     * @param  string        $name      Display name.
     * @param  string        $email     E-mail address (must be unique per tenant).
     * @param  string        $password  Plaintext password (will be hashed).
     * @param  string        $tenantId  Owning tenant UUID.
     * @param  array<string> $roles     Initial role assignments (default: viewer).
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $tenantId,
        public array $roles = ['viewer'],
    ) {}
}
