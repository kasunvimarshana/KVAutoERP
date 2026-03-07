<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    protected Model $model;
    protected array $relations = [];
    protected ?string $tenantId = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function newQuery(): Builder
    {
        $query = $this->model->newQuery();
        if (!empty($this->relations)) {
            $query->with($this->relations);
        }
        if ($this->tenantId !== null) {
            $query->where('tenant_id', $this->tenantId);
        }
        return $query;
    }

    public function all(): Collection
    {
        return $this->newQuery()->get();
    }

    public function find(string|int $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function findBy(string $column, mixed $value): ?Model
    {
        return $this->newQuery()->where($column, $value)->first();
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(string|int $id, array $data): ?Model
    {
        $record = $this->find($id);
        if ($record === null) {
            return null;
        }
        $record->fill($data)->save();
        return $record->fresh($this->relations);
    }

    public function delete(string|int $id): bool
    {
        $record = $this->find($id);
        if ($record === null) {
            return false;
        }
        return (bool) $record->delete();
    }

    public function paginate(?int $perPage = null, int $page = 1): LengthAwarePaginator|Collection
    {
        if ($perPage === null) {
            return $this->all();
        }
        return $this->newQuery()->paginate($perPage, ['*'], 'page', $page);
    }

    public function filter(array $filters): Builder
    {
        $query = $this->newQuery();
        foreach ($filters as $filter) {
            $column   = $filter['column']   ?? null;
            $operator = $filter['operator'] ?? 'eq';
            $value    = $filter['value']    ?? null;
            if ($column === null) {
                continue;
            }
            match ($operator) {
                'eq'      => $query->where($column, '=', $value),
                'like'    => $query->where($column, 'LIKE', '%' . $value . '%'),
                'gt'      => $query->where($column, '>', $value),
                'lt'      => $query->where($column, '<', $value),
                'between' => $query->whereBetween($column, (array) $value),
                'in'      => $query->whereIn($column, (array) $value),
                default   => $query->where($column, '=', $value),
            };
        }
        return $query;
    }

    public function search(string $term, array $columns): Builder
    {
        $query = $this->newQuery();
        $query->where(function (Builder $q) use ($term, $columns): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $q->{$method}($column, 'LIKE', '%' . $term . '%');
            }
        });
        return $query;
    }

    public function sort(string $column, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        return $this->newQuery()->orderBy($column, $direction);
    }

    public function withRelations(array $relations): static
    {
        $clone            = clone $this;
        $clone->relations = array_merge($clone->relations, $relations);
        return $clone;
    }

    public function withTenant(string $tenantId): static
    {
        $clone           = clone $this;
        $clone->tenantId = $tenantId;
        return $clone;
    }

    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    public function resetScope(): static
    {
        $this->relations = [];
        $this->tenantId  = null;
        return $this;
    }
}
