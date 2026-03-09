<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

/**
 * Base Repository.
 *
 * Fully reusable, dynamic base repository implementing CRUD operations,
 * conditional pagination, filtering, searching, and sorting.
 *
 * Extend this class for any entity without modifying core logic.
 *
 * Usage:
 *   - Returns paginated results when 'per_page' exists in $params
 *   - Returns all results otherwise
 *   - Supports 'page' and 'per_page' for flexible pagination
 *   - Supports 'search', 'sort_by', 'sort_dir', 'filters' params
 *   - Supports 'with' for eager loading relationships
 *
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /**
     * The Eloquent model managed by this repository.
     *
     * @var TModel
     */
    protected Model $model;

    /**
     * Columns that are searchable via the 'search' parameter.
     *
     * @var string[]
     */
    protected array $searchableColumns = [];

    /**
     * Columns that are filterable via the 'filters' parameter.
     *
     * @var string[]
     */
    protected array $filterableColumns = [];

    /**
     * Default sort column.
     *
     * @var string
     */
    protected string $defaultSortBy = 'created_at';

    /**
     * Default sort direction.
     *
     * @var string
     */
    protected string $defaultSortDir = 'desc';

    /**
     * Maximum records per page (prevents abuse).
     *
     * @var int
     */
    protected int $maxPerPage = 200;

    /**
     * Default records per page when per_page param is present but invalid.
     *
     * @var int
     */
    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->model = $this->resolveModel();
    }

    /**
     * Resolve and return a fresh model instance.
     *
     * Subclasses may override to customize model resolution.
     *
     * @return TModel
     */
    abstract protected function resolveModel(): Model;

    /**
     * Get a new query builder scoped to the current tenant when applicable.
     *
     * @return Builder<TModel>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Retrieve records with optional filters, searching, sorting, and
     * conditional pagination.
     *
     * Returns a paginated result when 'per_page' is present in $params,
     * or all matching records otherwise.
     *
     * Supported $params keys:
     *   search    (string)  - Full-text search across searchable columns
     *   filters   (array)   - Key-value pairs applied as WHERE conditions
     *   sort_by   (string)  - Column to sort by
     *   sort_dir  (string)  - 'asc' or 'desc'
     *   page      (int)     - Page number (used with per_page)
     *   per_page  (int)     - Items per page; triggers pagination when present
     *   with      (array)   - Eager-load relationships
     *
     * @param  array<string, mixed>                            $params
     * @return LengthAwarePaginator|Collection<int, TModel>
     */
    public function all(array $params = []): LengthAwarePaginator|Collection
    {
        $query = $this->newQuery();

        $query = $this->applyEagerLoading($query, $params);
        $query = $this->applySearch($query, $params);
        $query = $this->applyFilters($query, $params);
        $query = $this->applySorting($query, $params);

        return $this->applyPagination($query, $params);
    }

    /**
     * Find a single record by primary key.
     *
     * @param  int|string $id
     * @return TModel|null
     */
    public function find(int|string $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    /**
     * Find a record by primary key or throw ModelNotFoundException.
     *
     * @param  int|string $id
     * @return TModel
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id): Model
    {
        return $this->newQuery()->findOrFail($id);
    }

    /**
     * Find records matching the given key-value criteria.
     *
     * Supports the same $params as all() for sorting and pagination.
     *
     * @param  array<string, mixed>                            $criteria
     * @param  array<string, mixed>                            $params
     * @return LengthAwarePaginator|Collection<int, TModel>
     */
    public function findBy(array $criteria, array $params = []): LengthAwarePaginator|Collection
    {
        $mergedParams = array_merge($params, ['filters' => array_merge(
            Arr::get($params, 'filters', []),
            $criteria,
        )]);

        return $this->all($mergedParams);
    }

    /**
     * Find the first record matching the given key-value criteria.
     *
     * @param  array<string, mixed> $criteria
     * @return TModel|null
     */
    public function findOneBy(array $criteria): ?Model
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
     * Create a new record and return the persisted model.
     *
     * @param  array<string, mixed> $data
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update a record by primary key and return the updated model.
     *
     * @param  int|string           $id
     * @param  array<string, mixed> $data
     * @return TModel
     * @throws ModelNotFoundException
     */
    public function update(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh() ?? $record;
    }

    /**
     * Delete a record by primary key.
     *
     * @param  int|string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(int|string $id): bool
    {
        $record = $this->findOrFail($id);

        return (bool) $record->delete();
    }

    /**
     * Check whether a record with the given primary key exists.
     *
     * @param  int|string $id
     * @return bool
     */
    public function exists(int|string $id): bool
    {
        return $this->newQuery()->where(
            $this->model->getKeyName(),
            $id,
        )->exists();
    }

    /**
     * Count records matching the given criteria.
     *
     * @param  array<string, mixed> $criteria
     * @return int
     */
    public function count(array $criteria = []): int
    {
        $query = $this->newQuery();

        foreach ($criteria as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->count();
    }

    /**
     * Paginate an arbitrary iterable (array, Collection, API response, etc.).
     *
     * Enables the repository to paginate data from any source, not just
     * Eloquent queries - useful for cross-service data access.
     *
     * @param  iterable<mixed>      $items
     * @param  array<string, mixed> $params
     * @return LengthAwarePaginator|array<mixed>
     */
    public function paginateCollection(iterable $items, array $params = []): LengthAwarePaginator|array
    {
        $items = collect($items);

        if (!isset($params['per_page'])) {
            return $items->all();
        }

        $perPage  = $this->resolvePerPage($params);
        $page     = $this->resolvePage($params);
        $total    = $items->count();
        $slice    = $items->forPage($page, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    // =========================================================================
    // Protected Query Builders
    // =========================================================================

    /**
     * Apply eager loading of relationships to the query.
     *
     * @param  Builder<TModel>      $query
     * @param  array<string, mixed> $params
     * @return Builder<TModel>
     */
    protected function applyEagerLoading(Builder $query, array $params): Builder
    {
        $with = Arr::get($params, 'with', []);

        if (!empty($with)) {
            $query->with((array) $with);
        }

        return $query;
    }

    /**
     * Apply full-text search across searchable columns.
     *
     * @param  Builder<TModel>      $query
     * @param  array<string, mixed> $params
     * @return Builder<TModel>
     */
    protected function applySearch(Builder $query, array $params): Builder
    {
        $searchTerm = Arr::get($params, 'search');

        if (empty($searchTerm) || empty($this->searchableColumns)) {
            return $query;
        }

        $term = trim((string) $searchTerm);

        $query->where(function (Builder $q) use ($term): void {
            foreach ($this->searchableColumns as $column) {
                $q->orWhere($column, 'LIKE', "%{$term}%");
            }
        });

        return $query;
    }

    /**
     * Apply column-level filters to the query.
     *
     * Supports simple equality, 'in' arrays, null checks,
     * range filters (column_from / column_to), and date ranges.
     *
     * @param  Builder<TModel>      $query
     * @param  array<string, mixed> $params
     * @return Builder<TModel>
     */
    protected function applyFilters(Builder $query, array $params): Builder
    {
        $filters = Arr::get($params, 'filters', []);

        if (empty($filters) || !is_array($filters)) {
            return $query;
        }

        foreach ($filters as $column => $value) {
            if (!in_array($column, $this->filterableColumns, true)) {
                continue;
            }

            if (is_null($value)) {
                $query->whereNull($column);
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($column, $value);
                continue;
            }

            $query->where($column, $value);
        }

        // Range filters: 'column_from' and 'column_to' patterns
        foreach ($params as $key => $value) {
            if (str_ends_with($key, '_from')) {
                $col = substr($key, 0, -5);
                if (in_array($col, $this->filterableColumns, true)) {
                    $query->where($col, '>=', $value);
                }
            } elseif (str_ends_with($key, '_to')) {
                $col = substr($key, 0, -3);
                if (in_array($col, $this->filterableColumns, true)) {
                    $query->where($col, '<=', $value);
                }
            }
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     *
     * @param  Builder<TModel>      $query
     * @param  array<string, mixed> $params
     * @return Builder<TModel>
     */
    protected function applySorting(Builder $query, array $params): Builder
    {
        $sortBy  = Arr::get($params, 'sort_by', $this->defaultSortBy);
        $sortDir = strtolower((string) Arr::get($params, 'sort_dir', $this->defaultSortDir));

        // Validate direction to prevent SQL injection
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : $this->defaultSortDir;

        // Validate column against model's table columns
        $allowedColumns = array_merge(
            $this->searchableColumns,
            $this->filterableColumns,
            [$this->model->getKeyName(), 'created_at', 'updated_at'],
        );

        if (in_array($sortBy, $allowedColumns, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy($this->defaultSortBy, $this->defaultSortDir);
        }

        return $query;
    }

    /**
     * Apply conditional pagination to the query.
     *
     * Returns paginated results when 'per_page' is in $params,
     * otherwise returns a Collection of all matching records.
     *
     * @param  Builder<TModel>                                 $query
     * @param  array<string, mixed>                            $params
     * @return LengthAwarePaginator|Collection<int, TModel>
     */
    protected function applyPagination(Builder $query, array $params): LengthAwarePaginator|Collection
    {
        if (!array_key_exists('per_page', $params)) {
            return $query->get();
        }

        $perPage = $this->resolvePerPage($params);
        $page    = $this->resolvePage($params);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Resolve the per_page value from params with bounds enforcement.
     *
     * @param  array<string, mixed> $params
     * @return int
     */
    protected function resolvePerPage(array $params): int
    {
        $perPage = (int) Arr::get($params, 'per_page', $this->defaultPerPage);

        if ($perPage < 1) {
            return $this->defaultPerPage;
        }

        return min($perPage, $this->maxPerPage);
    }

    /**
     * Resolve the page number from params.
     *
     * @param  array<string, mixed> $params
     * @return int
     */
    protected function resolvePage(array $params): int
    {
        $page = (int) Arr::get($params, 'page', 1);

        return max(1, $page);
    }
}
