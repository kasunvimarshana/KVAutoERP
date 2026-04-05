<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\User;

interface UserServiceInterface
{
    public function createUser(array $data): User;
    public function updateUser(string $id, array $data): User;
    public function deleteUser(string $id): bool;
    public function getUser(string $id): User;
    public function getAllByTenant(string $tenantId): Collection;
}
