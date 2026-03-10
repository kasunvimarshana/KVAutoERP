<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Login Data Transfer Object
 */
final class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $tenantId,
        public readonly ?string $deviceName = null,
        public readonly bool $remember = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            tenantId: $data['tenant_id'],
            deviceName: $data['device_name'] ?? null,
            remember: (bool) ($data['remember'] ?? false),
        );
    }
}
