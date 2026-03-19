<?php

namespace Shared\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shared\Core\Contracts\RepositoryInterface;
use Illuminate\Container\Container as App;

abstract class BaseRepository implements RepositoryInterface
{
    /** @var App */
    protected $app;

    /** @var Model */
    protected $model;

    /** @var Builder */
    protected $query;

    /** @var array */
    protected $with = [];

    /** @var array */
    protected $where = [];

    /** @var array */
    protected $order = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
        $this->resetQuery();
    }

    abstract public function model(): string;

    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Model");
        }

        return $this->model = $model;
    }

    public function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
        $this->with = [];
        $this->where = [];
        $this->order = [];
    }

    public function all(array $columns = ['*']): Collection
    {
        $this->applyCriteria();
        $results = $this->query->get($columns);
        $this->resetQuery();
        return $results;
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $this->applyCriteria();
        $results = $this->query->paginate($perPage, $columns, $pageName, $page);
        $this->resetQuery();
        return $results;
    }

    public function find($id, array $columns = ['*']): ?Model
    {
        $this->applyCriteria();
        $result = $this->query->find($id, $columns);
        $this->resetQuery();
        return $result;
    }

    public function findBy(string $field, $value, array $columns = ['*']): ?Model
    {
        $this->applyCriteria();
        $result = $this->query->where($field, $value)->first($columns);
        $this->resetQuery();
        return $result;
    }

    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        $this->applyCriteria();
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $this->query->whereIn($field, $value);
            } else {
                $this->query->where($field, $value);
            }
        }
        $results = $this->query->get($columns);
        $this->resetQuery();
        return $results;
    }

    public function findWhereFirst(array $where, array $columns = ['*']): ?Model
    {
        $this->applyCriteria();
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $this->query->whereIn($field, $value);
            } else {
                $this->query->where($field, $value);
            }
        }
        $result = $this->query->first($columns);
        $this->resetQuery();
        return $result;
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id): bool
    {
        $model = $this->find($id);
        return $model ? $model->update($data) : false;
    }

    public function delete($id): bool
    {
        $model = $this->find($id);
        return $model ? $model->delete() : false;
    }

    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    public function where(string $column, $operator = null, $value = null, string $boolean = 'and'): self
    {
        $this->where[] = compact('column', 'operator', 'value', 'boolean');
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->order[] = compact('column', 'direction');
        return $this;
    }

    public function search(string $query, array $columns): self
    {
        $this->query->where(function ($q) use ($query, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$query}%");
            }
        });
        return $this;
    }

    protected function applyCriteria(): void
    {
        if (!empty($this->with)) {
            $this->query->with($this->with);
        }

        foreach ($this->where as $where) {
            $this->query->where($where['column'], $where['operator'], $where['value'], $where['boolean']);
        }

        foreach ($this->order as $order) {
            $this->query->orderBy($order['column'], $order['direction']);
        }
    }
}
