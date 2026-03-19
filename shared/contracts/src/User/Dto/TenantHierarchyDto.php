<?php

declare(strict_types=1);

namespace KvSaas\Contracts\User\Dto;

/**
 * Represents the full 5-level tenant hierarchy:
 * Tenant → Organisation → Branch → Location → Department
 */
final readonly class TenantHierarchyDto
{
    public function __construct(
        public string  $tenantId,
        public ?string $organizationId = null,
        public ?string $branchId       = null,
        public ?string $locationId     = null,
        public ?string $departmentId   = null,
    ) {}

    /** Returns the hierarchy as JWT claim keys. */
    public function toClaimsMap(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'org_id'    => $this->organizationId ?? '',
            'branch_id' => $this->branchId ?? '',
            'loc_id'    => $this->locationId ?? '',
            'dept_id'   => $this->departmentId ?? '',
        ];
    }
}
