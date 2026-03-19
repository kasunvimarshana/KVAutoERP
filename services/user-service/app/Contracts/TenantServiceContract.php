<?php

declare(strict_types=1);

namespace App\Contracts;

interface TenantServiceContract
{
    public function findById(string $tenantId): ?array;

    public function create(array $data): array;

    public function update(string $tenantId, array $data): array;

    public function delete(string $tenantId): void;

    public function getHierarchy(string $tenantId): array;

    public function list(array $filters = [], int $perPage = 20): array;
}
