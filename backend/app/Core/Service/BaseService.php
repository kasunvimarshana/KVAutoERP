<?php

namespace App\Core\Service;

use App\Core\Pagination\PaginationHelper;
use App\Core\Repository\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    public function __construct(protected BaseRepository $repository)
    {
    }

    public function index(array $params = []): array
    {
        $query = $this->repository->query();
        $this->applyFilters($query, $params);

        return PaginationHelper::paginate($query, $params);
    }

    public function show(int $id): Model
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function store(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Model
    {
        return $this->repository->update($id, $data);
    }

    public function destroy(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /** Override in sub-classes to add query filters. */
    protected function applyFilters(Builder $query, array $params): void {}
}
