<?php

declare(strict_types=1);

namespace KvSaas\Contracts\User\Dto;

/** Canonical user representation shared between Auth and User services. */
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
        /** @var string[] */
        public array   $roles,
        /** @var string[] */
        public array   $permissions,
        public int     $tokenVersion,
        public ?string $iamProvider = null,
        public ?string $externalId  = null,
        /** @var array<string, mixed> */
        public array   $attributes  = [],
    ) {}

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasFederatedIdentity(): bool
    {
        return $this->iamProvider !== null && $this->iamProvider !== 'local';
    }

    /** @return array<string, mixed> */
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

    /** @param array<string, mixed> $data */
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
