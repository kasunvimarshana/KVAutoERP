<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TokenClaimsDto
{
    public function __construct(
        public string $userId,
        public string $tenantId,
        public ?string $organisationId,
        public ?string $branchId,
        public ?string $locationId,
        public ?string $departmentId,
        public array $roles,
        public array $permissions,
        public string $deviceId,
        public int $tokenVersion,
        public ?int $ttlMinutes = null,
        public string $jti = '',
        public array $customClaims = [],
    ) {}

    public function toArray(): array
    {
        return [
            'sub'             => $this->userId,
            'user_id'         => $this->userId,
            'tenant_id'       => $this->tenantId,
            'organization_id' => $this->organisationId,
            'branch_id'       => $this->branchId,
            'location_id'     => $this->locationId,
            'department_id'   => $this->departmentId,
            'roles'           => $this->roles,
            'permissions'     => $this->permissions,
            'device_id'       => $this->deviceId,
            'token_version'   => $this->tokenVersion,
            'jti'             => $this->jti ?: \Ramsey\Uuid\Uuid::uuid4()->toString(),
        ] + $this->customClaims;
    }
}
