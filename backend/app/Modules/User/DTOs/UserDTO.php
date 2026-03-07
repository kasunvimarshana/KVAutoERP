<?php

namespace App\Modules\User\DTOs;

class UserDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $username,
        public readonly string $email,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly string $role = 'viewer',
        public readonly bool $isActive = true,
        public readonly ?array $permissions = null,
        public readonly ?string $password = null,
        public readonly ?string $keycloakId = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId:    $data['tenant_id'],
            username:    $data['username'],
            email:       $data['email'],
            firstName:   $data['first_name'] ?? null,
            lastName:    $data['last_name'] ?? null,
            role:        $data['role'] ?? 'viewer',
            isActive:    $data['is_active'] ?? true,
            permissions: $data['permissions'] ?? null,
            password:    $data['password'] ?? null,
            keycloakId:  $data['keycloak_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id'   => $this->tenantId,
            'username'    => $this->username,
            'email'       => $this->email,
            'first_name'  => $this->firstName,
            'last_name'   => $this->lastName,
            'role'        => $this->role,
            'is_active'   => $this->isActive,
            'permissions' => $this->permissions,
            'keycloak_id' => $this->keycloakId,
        ];
    }
}
