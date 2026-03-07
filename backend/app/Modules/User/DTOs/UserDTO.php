<?php

namespace App\Modules\User\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?int $tenantId = null,
        public readonly ?array $attributes = null,
        public readonly ?bool $isActive = null,
        public readonly ?string $role = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            attributes: $data['attributes'] ?? null,
            isActive: $data['is_active'] ?? null,
            role: $data['role'] ?? null,
        );
    }
}
