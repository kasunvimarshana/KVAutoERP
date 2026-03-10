<?php
namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery();
        $query = $this->applyFilters($query, $filters);
        $query = $this->applySearch($query, $params['search'] ?? null);
        $query = $this->applySort($query, $params['sort_by'] ?? null, $params['sort_dir'] ?? 'asc');

        if (isset($params['per_page'])) {
            return $query->paginate((int) $params['per_page'], ['*'], 'page', (int) ($params['page'] ?? 1));
        }
        return $query->get();
    }

    public function findById(string|int $id): ?Model { return $this->model->find($id); }
    public function findOrFail(string|int $id): Model { return $this->model->findOrFail($id); }
    public function findBy(string $col, mixed $val): ?Model { return $this->model->where($col, $val)->first(); }
    public function findWhere(array $criteria): Collection { return $this->model->where($criteria)->get(); }
    public function create(array $data): Model { return $this->model->create($data); }
    public function exists(array $criteria): bool { return $this->model->where($criteria)->exists(); }
    public function count(array $criteria = []): int {
        return empty($criteria) ? $this->model->count() : $this->model->where($criteria)->count();
    }

    public function update(string|int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(string|int $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            if (is_null($value)) continue;
            is_array($value) ? $query->whereIn($column, $value) : $query->where($column, $value);
        }
        return $query;
    }

    protected function applySearch(Builder $query, ?string $search): Builder
    {
        if (empty($search) || empty($this->searchableColumns())) return $query;
        $safe = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
        return $query->where(function (Builder $q) use ($safe) {
            foreach ($this->searchableColumns() as $col) {
                $q->orWhere($col, 'LIKE', "%{$safe}%");
            }
        });
    }

    protected function applySort(Builder $query, ?string $sortBy, string $sortDir = 'asc'): Builder
    {
        if ($sortBy && in_array($sortBy, $this->sortableColumns(), true)) {
            return $query->orderBy($sortBy, in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'asc');
        }
        return $query->orderBy($this->defaultSortColumn(), $this->defaultSortDirection());
    }

    protected function searchableColumns(): array { return []; }
    protected function sortableColumns(): array { return ['created_at', 'updated_at']; }
    protected function defaultSortColumn(): string { return 'created_at'; }
    protected function defaultSortDirection(): string { return 'desc'; }
}
