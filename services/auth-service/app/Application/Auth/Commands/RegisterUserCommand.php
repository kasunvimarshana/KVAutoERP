<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

/**
 * Command DTO for registering a new tenant user.
 */
final readonly class RegisterUserCommand
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public string $email,
        public string $password,
        public ?string $organizationId = null,
        /** @var list<string> */
        public array $roles = ['user'],
        public array $metadata = [],
    ) {}
}
