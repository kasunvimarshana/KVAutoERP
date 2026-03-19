<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Contracts;

interface TenantContextInterface
{
    /**
     * Return the current request's tenant ID from the JWT payload.
     */
    public function getTenantId(): string;

    /**
     * Return the current request's user ID.
     */
    public function getUserId(): string;

    /**
     * Return the current request's organisation ID (nullable).
     */
    public function getOrganisationId(): ?string;

    /**
     * Return the current request's branch ID (nullable).
     */
    public function getBranchId(): ?string;

    /**
     * Return the roles embedded in the current JWT.
     */
    public function getRoles(): array;

    /**
     * Return the permissions embedded in the current JWT.
     */
    public function getPermissions(): array;

    /**
     * Check whether the current user has the given permission.
     */
    public function hasPermission(string $permission): bool;

    /**
     * Check whether the current user has any of the given roles.
     */
    public function hasRole(string|array $roles): bool;

    /**
     * Set context from a decoded JWT payload.
     */
    public function setFromPayload(array $payload): void;

    /**
     * Return the full decoded payload.
     */
    public function getPayload(): array;
}
