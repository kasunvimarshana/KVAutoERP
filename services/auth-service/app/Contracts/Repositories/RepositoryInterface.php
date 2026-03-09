<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function findById(string|int $id): ?Model;

    public function findAll(array $filters = [], array $options = []): Collection|LengthAwarePaginator;

    public function create(array $data): Model;

    public function update(string|int $id, array $data): ?Model;

    public function delete(string|int $id): bool;

    public function exists(array $conditions): bool;

    public function count(array $filters = []): int;
}
