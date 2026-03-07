<?php

namespace App\DTOs;

use App\Models\User;

class UserDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $tenantId,
        public readonly string  $name,
        public readonly string  $email,
        public readonly string  $role,
        public readonly array   $permissions,
        public readonly string  $status,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id:          $user->id,
            tenantId:    $user->tenant_id,
            name:        $user->name,
            email:       $user->email,
            role:        $user->role,
            permissions: $user->permissions ?? [],
            status:      $user->status,
            createdAt:   $user->created_at?->toIso8601String(),
            updatedAt:   $user->updated_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenantId,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->role,
            'permissions' => $this->permissions,
            'status'      => $this->status,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}
