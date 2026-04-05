<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email, ?int $tenantId = null): ?User;

    /** @return Collection<int, User> */
    public function findByTenantId(int $tenantId): Collection;

    public function create(array $data): User;

    public function update(int $id, array $data): ?User;

    public function delete(int $id): bool;

    public function assignRole(int $userId, int $roleId): void;

    public function revokeRole(int $userId, int $roleId): void;

    /** @return Collection<int, \Modules\Auth\Domain\Entities\Role> */
    public function getRoles(int $userId): Collection;

    /**
     * Create a new Passport API token for the user and return the raw token string.
     */
    public function createToken(int $userId, string $name = 'api'): string;

    /**
     * Revoke the currently active Passport token for the user.
     */
    public function revokeCurrentToken(int $userId): void;

    /**
     * Find a user by a valid Passport access token string.
     */
    public function findByAccessToken(string $token): ?User;
}
