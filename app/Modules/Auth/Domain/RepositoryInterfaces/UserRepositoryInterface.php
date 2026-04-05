<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email, string $tenantId): ?User;
    public function create(array $data): User;
    public function update(string $id, array $data): User;
    public function delete(string $id): bool;
    public function allByTenant(string $tenantId): Collection;
}
