<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UserDto
{
    public function __construct(
        public string  $id,
        public string  $email,
        public string  $name,
        public string  $tenantId,
        public string  $organizationId,
        public string  $branchId,
        public string  $status,
        public array   $roles,
        public array   $permissions,
        public int     $tokenVersion,
        public ?string $iamProvider = null,
        public ?string $externalId  = null,
        public array   $attributes  = [],
    ) {}

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'email'           => $this->email,
            'name'            => $this->name,
            'tenant_id'       => $this->tenantId,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'status'          => $this->status,
            'roles'           => $this->roles,
            'permissions'     => $this->permissions,
            'token_version'   => $this->tokenVersion,
            'iam_provider'    => $this->iamProvider,
            'external_id'     => $this->externalId,
            'attributes'      => $this->attributes,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            name: $data['name'],
            tenantId: $data['tenant_id'],
            organizationId: $data['organization_id'] ?? '',
            branchId: $data['branch_id'] ?? '',
            status: $data['status'] ?? 'active',
            roles: $data['roles'] ?? [],
            permissions: $data['permissions'] ?? [],
            tokenVersion: (int) ($data['token_version'] ?? 1),
            iamProvider: $data['iam_provider'] ?? null,
            externalId: $data['external_id'] ?? null,
            attributes: $data['attributes'] ?? [],
        );
    }
}
