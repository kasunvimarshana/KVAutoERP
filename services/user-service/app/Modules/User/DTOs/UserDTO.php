<?php

namespace App\Modules\User\DTOs;

class UserDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $phone = null,
        public readonly array $roles = ['customer'],
        public readonly array $attributes = [],
        public readonly bool $isActive = true,
        public readonly ?string $keycloakId = null,
        public readonly ?string $password = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            username:    $data['username'],
            email:       $data['email'],
            firstName:   $data['first_name'],
            lastName:    $data['last_name'],
            phone:       $data['phone'] ?? null,
            roles:       $data['roles'] ?? ['customer'],
            attributes:  $data['attributes'] ?? [],
            isActive:    $data['is_active'] ?? true,
            keycloakId:  $data['keycloak_id'] ?? null,
            password:    $data['password'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'username'   => $this->username,
            'email'      => $this->email,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'phone'      => $this->phone,
            'roles'      => $this->roles,
            'attributes' => $this->attributes,
            'is_active'  => $this->isActive,
            'keycloak_id'=> $this->keycloakId,
        ], fn ($v) => $v !== null);
    }
}
