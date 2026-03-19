<?php

declare(strict_types=1);

namespace App\Contracts;

interface UserServiceContract
{
    public function findById(string $userId): ?array;

    public function findByEmail(string $email): ?array;

    public function findByExternalId(string $externalId, string $provider): ?array;

    public function validateCredentials(string $userId, string $password): bool;

    public function getUserClaims(string $userId): array;

    public function recordLoginEvent(string $userId, string $deviceId, string $ipAddress): void;

    public function incrementTokenVersion(string $userId): int;

    public function createUser(array $data): array;

    public function updateUser(string $userId, array $data): array;

    public function listUsers(string $tenantId, array $filters = [], int $perPage = 20): array;

    public function deleteUser(string $userId): void;
}
