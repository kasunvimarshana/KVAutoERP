<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Registration Data Transfer Object
 */
final class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $tenantId,
        public readonly string $role = 'user',
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            tenantId: $data['tenant_id'],
            role: $data['role'] ?? 'user',
            metadata: $data['metadata'] ?? [],
        );
    }
}
