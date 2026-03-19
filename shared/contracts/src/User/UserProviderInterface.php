<?php

declare(strict_types=1);

namespace KvSaas\Contracts\User;

use KvSaas\Contracts\User\Dto\UserDto;

/**
 * Contract for user lookup and credential validation.
 * Auth interacts with User exclusively through this interface.
 */
interface UserProviderInterface
{
    public function findById(string $userId): ?UserDto;

    public function findByEmail(string $email): ?UserDto;

    public function findByExternalId(string $externalId, string $provider): ?UserDto;

    public function validateCredentials(string $userId, string $password): bool;

    /** @return array<string, mixed> */
    public function getUserClaims(string $userId): array;

    /** @return string[] */
    public function getUserPermissions(string $userId, string $tenantId): array;

    /** @return string[] */
    public function getUserRoles(string $userId, string $tenantId): array;
}
