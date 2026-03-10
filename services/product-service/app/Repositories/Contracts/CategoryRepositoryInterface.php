<?php
namespace App\Repositories\Contracts;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection;
    public function findById(string $id): ?Category;
    public function create(array $data): Category;
    public function update(string $id, array $data): Category;
    public function delete(string $id): bool;
}
