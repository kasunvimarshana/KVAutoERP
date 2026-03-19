<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CreateUserDto
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?string $avatar = null,
        public ?string $organisationId = null,
        public ?string $branchId = null,
        public ?string $locationId = null,
        public ?string $departmentId = null,
        public bool $isActive = true,
        public array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'] ?? null,
            avatar: $data['avatar'] ?? null,
            organisationId: $data['organisation_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            locationId: $data['location_id'] ?? null,
            departmentId: $data['department_id'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            metadata: $data['metadata'] ?? [],
        );
    }
}
