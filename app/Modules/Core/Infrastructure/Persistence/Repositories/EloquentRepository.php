<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EloquentRepository extends BaseRepository
{
    protected Model $model;

    /**
     * Optional mapper that converts Eloquent models into Domain entities.
     *
     * @var callable(Model): mixed|null
     */
    protected $domainEntityMapper = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->provider = $model->newQuery();
    }

    /**
     * Configure a mapper for converting Eloquent models to Domain entities.
     *
     * @param  callable(Model): mixed  $mapper
     */
    protected function setDomainEntityMapper(callable $mapper): void
    {
        $this->domainEntityMapper = $mapper;
    }

    /**
     * Convert a model to a Domain entity when a mapper is configured.
     */
    protected function toDomainEntity(Model $model): mixed
    {
        if ($this->domainEntityMapper === null) {
            return $model;
        }

        return ($this->domainEntityMapper)($model);
    }

    /**
     * Convert a model collection to Domain entities.
     */
    protected function toDomainCollection(Collection $models): Collection
    {
        return $models->map(fn (Model $model) => $this->toDomainEntity($model));
    }

    /**
     * Find a record and convert it to a Domain entity.
     *
     * @return mixed
     */
    protected function findDomain($id, array $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        return $result instanceof Model ? $this->toDomainEntity($result) : $result;
    }

    /**
     * Get a collection and convert each model to a Domain entity.
     */
    protected function getDomain(array $columns = ['*']): Collection
    {
        return $this->toDomainCollection($this->get($columns));
    }

    /**
     * Paginate records and convert each model to a Domain entity.
     */
    protected function paginateDomain(?int $perPage = null, array $columns = ['*'], ?string $pageName = null, ?int $page = null): LengthAwarePaginator
    {
        $paginator = $this->paginate($perPage, $columns, $pageName, $page);

        return $paginator->through(fn (Model $model) => $this->toDomainEntity($model));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCriteria(): void
    {
        $this->resetProvider();

        // Apply eager loading
        if (! empty($this->with)) {
            $this->provider->with($this->with);
        }

        // Apply where clauses
        foreach ($this->wheres as $where) {
            $this->provider->where($where['column'], $where['operator'], $where['value'], $where['boolean']);
        }

        // Apply whereIn
        foreach ($this->whereIns as $whereIn) {
            $this->provider->whereIn($whereIn['column'], $whereIn['values'], $whereIn['boolean'], $whereIn['not']);
        }

        // Apply whereBetween
        foreach ($this->whereBetweens as $whereBetween) {
            $this->provider->whereBetween($whereBetween['column'], $whereBetween['values'], $whereBetween['boolean'], $whereBetween['not']);
        }

        // Apply whereNull
        foreach ($this->whereNulls as $whereNull) {
            $method = $whereNull['not'] ? 'whereNotNull' : 'whereNull';
            $this->provider->{$method}($whereNull['column'], $whereNull['boolean']);
        }

        // Apply orderBy
        foreach ($this->orders as $order) {
            $this->provider->orderBy($order['column'], $order['direction']);
        }

        // Apply orderByRaw
        foreach ($this->orderByRaw as $rawOrder) {
            $this->provider->orderByRaw($rawOrder['sql'], $rawOrder['bindings']);
        }

        // Apply limit and offset
        if ($this->limit !== null) {
            $this->provider->limit($this->limit);
        }
        if ($this->offset !== null) {
            $this->provider->offset($this->offset);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function resetProvider(): void
    {
        $this->provider = $this->model->newQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);

            return $record->fresh();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id): bool
    {
        $record = $this->find($id);
        if ($record) {
            return (bool) $record->delete();
        }

        return false;
    }
}
