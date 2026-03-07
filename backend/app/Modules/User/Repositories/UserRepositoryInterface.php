<?php

namespace App\Modules\User\Repositories;

interface UserRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function create(array $data): \App\Modules\User\Models\User;
    public function update(int $id, array $data): \App\Modules\User\Models\User;
    public function delete(int $id): bool;
    public function findByEmail(string $email): ?\App\Modules\User\Models\User;
    public function findByTenant(int $tenantId, array $filters = [], int $perPage = 15);
}
