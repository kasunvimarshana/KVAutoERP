<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Services;

use KvEnterprise\SharedAuth\Contracts\TenantContextInterface;

/**
 * Request-scoped tenant context populated from the decoded JWT payload.
 * Registered as a singleton within the request lifecycle, making it
 * available anywhere in the microservice without passing the payload around.
 */
class TenantContext implements TenantContextInterface
{
    private array $payload = [];

    public function setFromPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getTenantId(): string
    {
        return (string) ($this->payload['tenant_id'] ?? '');
    }

    public function getUserId(): string
    {
        return (string) ($this->payload['user_id'] ?? $this->payload['sub'] ?? '');
    }

    public function getOrganisationId(): ?string
    {
        return $this->payload['organization_id'] ?? null;
    }

    public function getBranchId(): ?string
    {
        return $this->payload['branch_id'] ?? null;
    }

    public function getRoles(): array
    {
        return (array) ($this->payload['roles'] ?? []);
    }

    public function getPermissions(): array
    {
        return (array) ($this->payload['permissions'] ?? []);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getPermissions();

        if (in_array($permission, $permissions, true)) {
            return true;
        }

        // Wildcard check
        foreach ($permissions as $userPermission) {
            if (str_ends_with($userPermission, '.*')) {
                $prefix = rtrim($userPermission, '.*');
                if (str_starts_with($permission, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasRole(string|array $roles): bool
    {
        $checkRoles = is_array($roles) ? $roles : [$roles];
        return ! empty(array_intersect($checkRoles, $this->getRoles()));
    }
}
