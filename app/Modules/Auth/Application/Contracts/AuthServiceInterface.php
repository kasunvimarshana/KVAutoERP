<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\Entities\User;

interface AuthServiceInterface
{
    /**
     * Authenticate a user and return an API token string.
     *
     * @param  array{email: string, password: string, tenant_id?: int}  $credentials
     */
    public function login(array $credentials): string;

    /**
     * Revoke the current access token for the given user.
     */
    public function logout(User $user): void;

    /**
     * Register a new user and return the created User entity.
     *
     * @param  array{name: string, email: string, password: string, tenant_id?: int, role?: string}  $data
     */
    public function register(array $data): User;

    /**
     * Refresh the API token for the given user, returning a new token string.
     */
    public function refreshToken(User $user): string;

    /**
     * Validate a raw token string and return the associated User, or null.
     */
    public function validateToken(string $token): ?User;

    /**
     * Assign a role to a user.
     */
    public function assignRole(int $userId, int $roleId): void;

    /**
     * Revoke a role from a user.
     */
    public function revokeRole(int $userId, int $roleId): void;
}
