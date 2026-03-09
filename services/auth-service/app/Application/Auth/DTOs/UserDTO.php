<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

use App\Domain\User\Entities\User;
use Illuminate\Support\Carbon;

/**
 * Data Transfer Object representing a user for API responses.
 */
final readonly class UserDTO
{
    public function __construct(
        public string $id,
        public string $tenantId,
        public ?string $organizationId,
        public string $name,
        public string $email,
        public string $status,
        public bool $emailVerified,
        public bool $twoFactorEnabled,
        public ?Carbon $lastLoginAt,
        public ?Carbon $emailVerifiedAt,
        public array $roles,
        public array $permissions,
        public array $metadata,
        public Carbon $createdAt,
        public Carbon $updatedAt,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->id,
            tenantId: $user->tenant_id,
            organizationId: $user->organization_id,
            name: $user->name,
            email: $user->email,
            status: $user->status,
            emailVerified: $user->isEmailVerified(),
            twoFactorEnabled: (bool) $user->two_factor_enabled,
            lastLoginAt: $user->last_login_at,
            emailVerifiedAt: $user->email_verified_at,
            roles: $user->getRoleNames()->toArray(),
            permissions: $user->getAllPermissions()->pluck('name')->toArray(),
            metadata: $user->metadata ?? [],
            createdAt: $user->created_at,
            updatedAt: $user->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'tenant_id'         => $this->tenantId,
            'organization_id'   => $this->organizationId,
            'name'              => $this->name,
            'email'             => $this->email,
            'status'            => $this->status,
            'email_verified'    => $this->emailVerified,
            'two_factor_enabled' => $this->twoFactorEnabled,
            'last_login_at'     => $this->lastLoginAt?->toIso8601String(),
            'email_verified_at' => $this->emailVerifiedAt?->toIso8601String(),
            'roles'             => $this->roles,
            'permissions'       => $this->permissions,
            'metadata'          => $this->metadata,
            'created_at'        => $this->createdAt->toIso8601String(),
            'updated_at'        => $this->updatedAt->toIso8601String(),
        ];
    }
}
