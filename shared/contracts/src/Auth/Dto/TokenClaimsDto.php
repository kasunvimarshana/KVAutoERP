<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Auth\Dto;

/**
 * Decoded, verified JWT claims — the canonical identity object
 * propagated across all microservices.
 */
final readonly class TokenClaimsDto
{
    public function __construct(
        public string $jti,
        public string $userId,
        public string $tenantId,
        public string $organizationId,
        public string $branchId,
        /** @var string[] */
        public array  $roles,
        /** @var string[] */
        public array  $permissions,
        public string $deviceId,
        public int    $tokenVersion,
        public string $provider,
        public string $issuer,
        public int    $exp,
        public int    $iat,
    ) {}

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function isExpired(): bool
    {
        return time() > $this->exp;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'jti'           => $this->jti,
            'sub'           => $this->userId,
            'tenant_id'     => $this->tenantId,
            'org_id'        => $this->organizationId,
            'branch_id'     => $this->branchId,
            'roles'         => $this->roles,
            'permissions'   => $this->permissions,
            'device_id'     => $this->deviceId,
            'token_version' => $this->tokenVersion,
            'provider'      => $this->provider,
            'iss'           => $this->issuer,
            'exp'           => $this->exp,
            'iat'           => $this->iat,
        ];
    }
}
