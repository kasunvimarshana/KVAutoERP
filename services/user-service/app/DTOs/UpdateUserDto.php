<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UpdateUserDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $avatar = null,
        public ?string $organisationId = null,
        public ?string $branchId = null,
        public ?string $locationId = null,
        public ?string $departmentId = null,
        public ?bool $isActive = null,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            avatar: $data['avatar'] ?? null,
            organisationId: $data['organisation_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            locationId: $data['location_id'] ?? null,
            departmentId: $data['department_id'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'avatar'          => $this->avatar,
            'organisation_id' => $this->organisationId,
            'branch_id'       => $this->branchId,
            'location_id'     => $this->locationId,
            'department_id'   => $this->departmentId,
            'is_active'       => $this->isActive,
            'metadata'        => $this->metadata,
        ], fn (mixed $value): bool => $value !== null);
    }
}
