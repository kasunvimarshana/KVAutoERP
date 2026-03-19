<?php

declare(strict_types=1);

namespace App\Services;

use KvEnterprise\SharedKernel\Contracts\Auth\AuthContextInterface;
use Illuminate\Http\Request;

/**
 * Request-scoped authentication context.
 *
 * Populated by VerifyJwtMiddleware after successful token verification.
 * Provides zero-cost access to the authenticated identity throughout
 * the request lifecycle without additional Redis or DB lookups.
 */
final class AuthContext implements AuthContextInterface
{
    /** @var array<string, mixed>|null */
    private ?array $claims = null;

    /**
     * Hydrate the context from verified JWT claims.
     *
     * Called by VerifyJwtMiddleware after signature + revocation checks pass.
     *
     * @param  array<string, mixed>  $claims  Decoded and verified JWT payload.
     * @return void
     */
    public function hydrate(array $claims): void
    {
        $this->claims = $claims;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser(): ?array
    {
        return $this->claims;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserId(): ?string
    {
        return isset($this->claims['user_id']) ? (string) $this->claims['user_id'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTenantId(): ?string
    {
        return isset($this->claims['tenant_id']) ? (string) $this->claims['tenant_id'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrganizationId(): ?string
    {
        return isset($this->claims['organization_id']) ? (string) $this->claims['organization_id'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getBranchId(): ?string
    {
        return isset($this->claims['branch_id']) ? (string) $this->claims['branch_id'] : null;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        return (array) ($this->claims['roles'] ?? []);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        return (array) ($this->claims['permissions'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeviceId(): ?string
    {
        return isset($this->claims['device_id']) ? (string) $this->claims['device_id'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated(): bool
    {
        return $this->claims !== null && isset($this->claims['user_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenVersion(): ?int
    {
        return isset($this->claims['token_version']) ? (int) $this->claims['token_version'] : null;
    }
}
