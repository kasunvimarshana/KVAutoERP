<?php

declare(strict_types=1);

namespace App\Core\Abstracts\Repositories;

use App\Core\Contracts\Repositories\RepositoryInterface;
use App\Core\Traits\HasConditionalPagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * BaseRepository
 *
 * A fully dynamic, reusable, and extensible base repository that handles:
 *  - Full CRUD operations
 *  - Conditional pagination (page + per_page aware)
 *  - Dynamic filtering, searching, and sorting
 *  - Cross-service / external data-source pagination
 *  - Eager loading of relations
 *
 * Extend this class for any Eloquent entity. Override the model property
 * and optionally customise searchable/filterable column lists.
 *
 * @template TModel of Model
 */
abstract class BaseRepository implements RepositoryInterface
{
    use HasConditionalPagination;

    /** @var class-string<TModel> Eloquent model class to operate on */
    protected string $model;

    /** @var array<string> Columns that support full-text LIKE search */
    protected array $searchableColumns = [];

    /** @var array<string> Columns that can be used as filters */
    protected array $filterableColumns = [];

    /** @var array<string> Columns that are allowed as sort targets */
    protected array $sortableColumns = ['created_at', 'updated_at'];

    /** @var string Default sort column */
    protected string $defaultSortColumn = 'created_at';

    /** @var string Default sort direction */
    protected string $defaultSortDirection = 'desc';

    // -------------------------------------------------------------------------
    //  Core CRUD
    // -------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function all(
        array $filters = [],
        array $sort = [],
        array $with = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        $query = $this->newQuery();

        $this->applyEagerLoads($query, $with);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $sort);

        return $this->applyPagination($query, $perPage, $page);
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int|string $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria): ?Model
    {
        $query = $this->newQuery();

        foreach ($criteria as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            return $this->newQuery()->create($data);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update(int|string $id, array $data): Model
    {
        return DB::transaction(function () use ($id, $data): Model {
            $model = $this->findOrFail($id);
            $model->update($data);
            return $model->refresh();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int|string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $model = $this->findOrFail($id);
            return (bool) $model->delete();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function search(
        string $term,
        array $columns = [],
        array $filters = [],
        array $sort = [],
        array $with = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection {
        $searchColumns = $columns ?: $this->searchableColumns;

        if (empty($searchColumns)) {
            throw new InvalidArgumentException(
                "No searchable columns defined for [" . static::class . "]."
            );
        }

        $query = $this->newQuery();

        $this->applyEagerLoads($query, $with);
        $this->applyFilters($query, $filters);

        $query->where(function (Builder $q) use ($term, $searchColumns): void {
            foreach ($searchColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $q->{$method}($column, 'LIKE', "%{$term}%");
            }
        });

        $this->applySorting($query, $sort);

        return $this->applyPagination($query, $perPage, $page);
    }

    /**
     * {@inheritdoc}
     */
    public function paginateData(
        iterable $data,
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|array {
        return $this->paginateIterable($data, $perPage, $page);
    }

    // -------------------------------------------------------------------------
    //  Protected helpers
    // -------------------------------------------------------------------------

    /**
     * Create a new query builder for this repository's model.
     *
     * @return Builder<TModel>
     */
    protected function newQuery(): Builder
    {
        return app($this->model)->newQuery();
    }

    /**
     * Find a model or throw a RuntimeException.
     *
     * @param  int|string $id
     * @return TModel
     *
     * @throws RuntimeException
     */
    protected function findOrFail(int|string $id): Model
    {
        $model = $this->findById($id);

        if ($model === null) {
            throw new RuntimeException(
                class_basename($this->model) . " with ID [{$id}] not found."
            );
        }

        return $model;
    }

    /**
     * Apply eager-load relations to a query.
     *
     * @param  Builder<TModel>  $query
     * @param  array<string>    $with
     */
    protected function applyEagerLoads(Builder $query, array $with): void
    {
        if (! empty($with)) {
            $query->with($with);
        }
    }

    /**
     * Apply dynamic filters to a query.
     *
     * Supported filter formats:
     *   'column'         => 'value'           → WHERE column = 'value'
     *   'column'         => ['a', 'b']        → WHERE column IN ('a', 'b')
     *   'column:like'    => '%term%'           → WHERE column LIKE '%term%'
     *   'column:>'       => 5                 → WHERE column > 5
     *   'column:between' => [min, max]        → WHERE column BETWEEN min AND max
     *
     * @param  Builder<TModel>  $query
     * @param  array<string,mixed> $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $rawKey => $value) {
            // Parse optional operator suffix: "column:operator"
            [$column, $operator] = array_pad(explode(':', (string) $rawKey, 2), 2, '=');

            $operator = strtolower($operator ?? '=');

            // Only allow known filterable columns (if the list is defined)
            if (! empty($this->filterableColumns) && ! in_array($column, $this->filterableColumns, true)) {
                continue;
            }

            match ($operator) {
                'like'    => $query->where($column, 'LIKE', $value),
                'between' => $query->whereBetween($column, (array) $value),
                'in'      => $query->whereIn($column, (array) $value),
                'notin'   => $query->whereNotIn($column, (array) $value),
                'null'    => $query->whereNull($column),
                'notnull' => $query->whereNotNull($column),
                default   => is_array($value)
                    ? $query->whereIn($column, $value)
                    : $query->where($column, $operator === '=' ? '=' : $operator, $value),
            };
        }
    }

    /**
     * Apply sorting to a query.
     *
     * @param  Builder<TModel>      $query
     * @param  array<string,string> $sort  ['column' => 'asc|desc', ...]
     */
    protected function applySorting(Builder $query, array $sort): void
    {
        if (empty($sort)) {
            $query->orderBy($this->defaultSortColumn, $this->defaultSortDirection);
            return;
        }

        foreach ($sort as $column => $direction) {
            if (
                ! empty($this->sortableColumns)
                && ! in_array($column, $this->sortableColumns, true)
            ) {
                continue;
            }

            $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($column, $direction);
        }
    }

    /**
     * Apply conditional pagination to an Eloquent query builder.
     *
     * Returns a paginator when $perPage is given, or a plain Collection otherwise.
     *
     * @param  Builder<TModel> $query
     * @param  int|null        $perPage
     * @param  int             $page
     * @return LengthAwarePaginator|Collection
     */
    protected function applyPagination(
        Builder $query,
        ?int $perPage,
        int $page
    ): LengthAwarePaginator|Collection {
        if ($perPage !== null && $perPage > 0) {
            return $query->paginate($perPage, ['*'], 'page', $page);
        }

        return $query->get();
    }
}
