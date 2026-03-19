<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\UserDto;

interface UserServiceClientContract
{
    public function findById(string $userId): ?UserDto;

    public function findByEmail(string $email): ?UserDto;

    public function findByExternalId(string $externalId, string $provider): ?UserDto;

    public function validateCredentials(string $userId, string $password): bool;

    public function getUserClaims(string $userId): array;

    public function recordLoginEvent(string $userId, string $deviceId, string $ipAddress): void;

    public function incrementTokenVersion(string $userId): int;

    /**
     * Fetch the tenant's runtime IAM configuration from the User service.
     *
     * @return array{iam_provider: string, iam_config: array<string, mixed>, status: string}
     */
    public function getTenantIamConfig(string $tenantId): array;
}
