<?php

declare(strict_types=1);

namespace Shared\BaseRepository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Closure;
use Exception;
use InvalidArgumentException;

/**
 * Fully Dynamic, Reusable Base Repository
 * 
 * Implements the Repository Pattern for Clean Architecture.
 * Supports dynamic CRUD operations, conditional pagination, filtering,
 * searching, sorting, relations, cross-service data access, and can handle
 * queries, arrays, collections, or API responses.
 * 
 * Features:
 * - Dynamic CRUD with conditional pagination
 * - Advanced filtering, searching, sorting
 * - Eager/lazy relation loading
 * - Cross-service data merging
 * - Query result caching
 * - Soft delete support
 * - Scoped queries
 * - Full-text search support
 * - Cursor-based pagination
 * - Aggregate queries
 * 
 * @template TModel of Model
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /** @var TModel */
    protected Model $model;

    /**
     * Default number of records per page.
     * Can be overridden per query.
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum allowed records per page.
     * Prevents abuse / memory exhaustion.
     */
    protected int $maxPerPage = 1000;

    /**
     * Default cache TTL in seconds.
     */
    protected int $cacheTtl = 300;

    /**
     * Whether caching is enabled.
     */
    protected bool $cacheEnabled = false;

    /**
     * Columns allowed for sorting.
     * Empty array means all columns are sortable.
     */
    protected array $sortableColumns = [];

    /**
     * Columns allowed for filtering.
     * Empty array means all columns are filterable.
     */
    protected array $filterableColumns = [];

    /**
     * Columns to search in when using full-text search.
     */
    protected array $searchableColumns = [];

    /**
     * Default relations to always eager load.
     */
    protected array $defaultRelations = [];

    /**
     * BaseRepository constructor.
     * 
     * @param TModel $model The Eloquent model instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get the underlying Eloquent model.
     * 
     * @return TModel
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Get a fresh query builder instance.
     * 
     * @return Builder<TModel>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    // =========================================================================
    // BASIC CRUD OPERATIONS
    // =========================================================================

    /**
     * Find a record by its primary key.
     * 
     * @param int|string $id Primary key value
     * @param array $columns Columns to select (default: all)
     * @param array $relations Relations to eager load
     * @param bool $fail Throw exception if not found
     * @return TModel|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If $fail=true and not found
     */
    public function findById(
        int|string $id,
        array $columns = ['*'],
        array $relations = [],
        bool $fail = false
    ): ?Model {
        $query = $this->newQuery()->select($columns);

        if (!empty($relations)) {
            $query->with($relations);
        } elseif (!empty($this->defaultRelations)) {
            $query->with($this->defaultRelations);
        }

        return $fail
            ? $query->findOrFail($id)
            : $query->find($id);
    }

    /**
     * Find a record by a specific column value.
     * 
     * @param string $column Column name
     * @param mixed $value Value to search for
     * @param array $columns Columns to select
     * @param array $relations Relations to eager load
     * @return TModel|null
     */
    public function findBy(
        string $column,
        mixed $value,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        $query = $this->newQuery()
            ->select($columns)
            ->where($column, $value);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Find multiple records by a specific column value.
     * 
     * @param string $column Column name
     * @param mixed $value Value to search for
     * @param array $columns Columns to select
     * @param array $relations Relations to eager load
     * @return Collection<int, TModel>
     */
    public function findAllBy(
        string $column,
        mixed $value,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->newQuery()
            ->select($columns)
            ->where($column, $value);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Find records by multiple conditions (array of column => value pairs).
     * 
     * @param array $conditions Array of [column => value] or [column, operator, value]
     * @param array $columns Columns to select
     * @param array $relations Relations to eager load
     * @return Collection<int, TModel>
     */
    public function findWhere(
        array $conditions,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->applyConditions($this->newQuery()->select($columns), $conditions);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get the first record matching conditions.
     * 
     * @param array $conditions Array of conditions
     * @param array $columns Columns to select
     * @param array $relations Relations to eager load
     * @return TModel|null
     */
    public function firstWhere(
        array $conditions,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        $query = $this->applyConditions($this->newQuery()->select($columns), $conditions);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Get all records.
     * 
     * @param array $columns Columns to select
     * @param array $relations Relations to eager load
     * @param array $orderBy [[column, direction], ...]
     * @return Collection<int, TModel>
     */
    public function all(
        array $columns = ['*'],
        array $relations = [],
        array $orderBy = []
    ): Collection {
        $query = $this->newQuery()->select($columns);

        if (!empty($relations)) {
            $query->with($relations);
        } elseif (!empty($this->defaultRelations)) {
            $query->with($this->defaultRelations);
        }

        foreach ($orderBy as [$column, $direction]) {
            $query->orderBy($column, $direction ?? 'asc');
        }

        return $query->get();
    }

    /**
     * Create a new record.
     * 
     * @param array $data Data to create the record with
     * @return TModel The created model instance
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    /**
     * Create multiple records efficiently using a bulk INSERT.
     * 
     * ⚠ Note: `Model::insert()` bypasses Eloquent model events, auto-timestamps,
     * and model traits (HasUuid, HasAuditLog, etc.). Use only when raw performance
     * is required and you are managing timestamps / UUIDs in the supplied $data array.
     * 
     * @param array $data Array of data arrays
     * @param int $chunkSize Chunk size for batch inserts
     * @return bool
     */
    public function createMany(array $data, int $chunkSize = 500): bool
    {
        return DB::transaction(function () use ($data, $chunkSize) {
            foreach (array_chunk($data, $chunkSize) as $chunk) {
                $this->model->insert($chunk);
            }
            return true;
        });
    }

    /**
     * Update a record by its primary key.
     * 
     * @param int|string $id Primary key value
     * @param array $data Data to update
     * @return TModel|null The updated model instance, or null if not found
     */
    public function update(int|string $id, array $data): ?Model
    {
        return DB::transaction(function () use ($id, $data) {
            $record = $this->findById($id);
            if (!$record) {
                return null;
            }
            $record->fill($data)->save();
            return $record->fresh();
        });
    }

    /**
     * Update records matching conditions.
     * 
     * @param array $conditions Array of conditions
     * @param array $data Data to update
     * @return int Number of affected rows
     */
    public function updateWhere(array $conditions, array $data): int
    {
        return DB::transaction(function () use ($conditions, $data) {
            return $this->applyConditions($this->newQuery(), $conditions)->update($data);
        });
    }

    /**
     * Create or update a record based on conditions.
     * 
     * @param array $conditions Conditions to find the record
     * @param array $data Data to create/update with (merged with conditions)
     * @return TModel
     */
    public function updateOrCreate(array $conditions, array $data): Model
    {
        return DB::transaction(function () use ($conditions, $data) {
            return $this->model->updateOrCreate($conditions, $data);
        });
    }

    /**
     * Delete a record by its primary key.
     * 
     * @param int|string $id Primary key value
     * @param bool $force Force hard delete even if soft deletes enabled
     * @return bool
     */
    public function delete(int|string $id, bool $force = false): bool
    {
        return DB::transaction(function () use ($id, $force) {
            $record = $this->findById($id);
            if (!$record) {
                return false;
            }
            return $force ? $record->forceDelete() : $record->delete();
        });
    }

    /**
     * Delete records matching conditions.
     * 
     * @param array $conditions Array of conditions
     * @param bool $force Force hard delete
     * @return int Number of deleted records
     */
    public function deleteWhere(array $conditions, bool $force = false): int
    {
        return DB::transaction(function () use ($conditions, $force) {
            $query = $this->applyConditions($this->newQuery(), $conditions);
            if ($force) {
                return $query->forceDelete();
            }
            return $query->delete();
        });
    }

    /**
     * Restore a soft-deleted record.
     * 
     * @param int|string $id Primary key value
     * @return bool
     */
    public function restore(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            return (bool) $this->newQuery()->withTrashed()->find($id)?->restore();
        });
    }

    // =========================================================================
    // PAGINATION OPERATIONS
    // =========================================================================

    /**
     * Get paginated results with full filtering, sorting, searching support.
     * 
     * Supports the following query parameters:
     * - page: Current page (default: 1)
     * - per_page: Records per page (default: $defaultPerPage, max: $maxPerPage)
     * - columns: Comma-separated list of columns to select
     * - sort_by: Column to sort by
     * - sort_dir: Sort direction (asc/desc)
     * - search: Search term (applied to $searchableColumns)
     * - filters: Array of [column => value] filters
     * - with: Comma-separated list of relations to load
     * - page_name: Custom page parameter name (default: 'page')
     * 
     * @param array $params Query parameters
     * @param array $additionalConditions Additional conditions to apply
     * @param Closure|null $queryCallback Optional callback to modify the query
     * @return LengthAwarePaginator<TModel>
     */
    public function paginate(
        array $params = [],
        array $additionalConditions = [],
        ?Closure $queryCallback = null
    ): LengthAwarePaginator {
        $perPage = $this->resolvePerPage($params);
        $page = (int) ($params['page'] ?? 1);
        $pageName = $params['page_name'] ?? 'page';
        $columns = $this->resolveColumns($params);

        $query = $this->newQuery()->select($columns);

        // Apply default relations
        $relations = $this->resolveRelations($params);
        if (!empty($relations)) {
            $query->with($relations);
        }

        // Apply additional conditions
        if (!empty($additionalConditions)) {
            $query = $this->applyConditions($query, $additionalConditions);
        }

        // Apply dynamic filters
        if (!empty($params['filters'])) {
            $query = $this->applyFilters($query, $params['filters']);
        }

        // Apply search
        if (!empty($params['search']) && !empty($this->searchableColumns)) {
            $query = $this->applySearch($query, $params['search']);
        }

        // Apply sorting
        $query = $this->applySorting($query, $params);

        // Apply custom query modifications
        if ($queryCallback !== null) {
            $query = $queryCallback($query) ?? $query;
        }

        return $query->paginate(
            perPage: $perPage,
            columns: ['*'],
            pageName: $pageName,
            page: $page
        );
    }

    /**
     * Get cursor-paginated results (efficient for large datasets).
     * 
     * @param array $params Query parameters
     * @param array $additionalConditions Additional conditions
     * @param Closure|null $queryCallback Optional query callback
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function cursorPaginate(
        array $params = [],
        array $additionalConditions = [],
        ?Closure $queryCallback = null
    ) {
        $perPage = $this->resolvePerPage($params);
        $columns = $this->resolveColumns($params);

        $query = $this->newQuery()->select($columns);

        $relations = $this->resolveRelations($params);
        if (!empty($relations)) {
            $query->with($relations);
        }

        if (!empty($additionalConditions)) {
            $query = $this->applyConditions($query, $additionalConditions);
        }

        if (!empty($params['filters'])) {
            $query = $this->applyFilters($query, $params['filters']);
        }

        if (!empty($params['search']) && !empty($this->searchableColumns)) {
            $query = $this->applySearch($query, $params['search']);
        }

        $query = $this->applySorting($query, $params);

        if ($queryCallback !== null) {
            $query = $queryCallback($query) ?? $query;
        }

        return $query->cursorPaginate(perPage: $perPage);
    }

    /**
     * Get results as a simple (non-counting) paginator.
     * More efficient when total count is not needed.
     * 
     * @param array $params Query parameters
     * @param array $additionalConditions Additional conditions
     * @param Closure|null $queryCallback Optional query callback
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate(
        array $params = [],
        array $additionalConditions = [],
        ?Closure $queryCallback = null
    ) {
        $perPage = $this->resolvePerPage($params);
        $page = (int) ($params['page'] ?? 1);
        $pageName = $params['page_name'] ?? 'page';
        $columns = $this->resolveColumns($params);

        $query = $this->newQuery()->select($columns);

        $relations = $this->resolveRelations($params);
        if (!empty($relations)) {
            $query->with($relations);
        }

        if (!empty($additionalConditions)) {
            $query = $this->applyConditions($query, $additionalConditions);
        }

        if (!empty($params['filters'])) {
            $query = $this->applyFilters($query, $params['filters']);
        }

        if (!empty($params['search']) && !empty($this->searchableColumns)) {
            $query = $this->applySearch($query, $params['search']);
        }

        $query = $this->applySorting($query, $params);

        if ($queryCallback !== null) {
            $query = $queryCallback($query) ?? $query;
        }

        return $query->simplePaginate(
            perPage: $perPage,
            columns: ['*'],
            pageName: $pageName,
            page: $page
        );
    }

    // =========================================================================
    // QUERY HELPERS
    // =========================================================================

    /**
     * Check if a record exists.
     * 
     * @param array $conditions Conditions to check
     * @return bool
     */
    public function exists(array $conditions): bool
    {
        return $this->applyConditions($this->newQuery(), $conditions)->exists();
    }

    /**
     * Count records matching conditions.
     * 
     * @param array $conditions Conditions to count
     * @return int
     */
    public function count(array $conditions = []): int
    {
        $query = $this->newQuery();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->count();
    }

    /**
     * Get the maximum value of a column.
     * 
     * @param string $column Column name
     * @param array $conditions Optional conditions
     * @return mixed
     */
    public function max(string $column, array $conditions = []): mixed
    {
        $query = $this->newQuery();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->max($column);
    }

    /**
     * Get the minimum value of a column.
     * 
     * @param string $column Column name
     * @param array $conditions Optional conditions
     * @return mixed
     */
    public function min(string $column, array $conditions = []): mixed
    {
        $query = $this->newQuery();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->min($column);
    }

    /**
     * Get the sum of a column.
     * 
     * @param string $column Column name
     * @param array $conditions Optional conditions
     * @return float|int
     */
    public function sum(string $column, array $conditions = []): float|int
    {
        $query = $this->newQuery();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->sum($column);
    }

    /**
     * Get the average of a column.
     * 
     * @param string $column Column name
     * @param array $conditions Optional conditions
     * @return float|null
     */
    public function avg(string $column, array $conditions = []): ?float
    {
        $query = $this->newQuery();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->avg($column);
    }

    /**
     * Get distinct values of a column.
     * 
     * @param string $column Column name
     * @param array $conditions Optional conditions
     * @return Collection
     */
    public function distinct(string $column, array $conditions = []): Collection
    {
        $query = $this->newQuery()->select($column)->distinct();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->get();
    }

    /**
     * Get records with pluck (key-value mapping).
     * 
     * @param string $valueColumn Column for values
     * @param string|null $keyColumn Column for keys (optional)
     * @param array $conditions Optional conditions
     * @return \Illuminate\Support\Collection
     */
    public function pluck(string $valueColumn, ?string $keyColumn = null, array $conditions = [])
    {
        $query = $this->newQuery();
        if (!empty($conditions)) {
            $query = $this->applyConditions($query, $conditions);
        }
        return $query->pluck($valueColumn, $keyColumn);
    }

    /**
     * Execute a raw query with parameter binding.
     * 
     * @param string $query Raw SQL query
     * @param array $bindings Query bindings
     * @return Collection
     */
    public function rawQuery(string $query, array $bindings = []): Collection
    {
        $results = DB::select($query, $bindings);
        return collect($results);
    }

    // =========================================================================
    // CROSS-SERVICE / API DATA ACCESS
    // =========================================================================

    /**
     * Merge local collection with data from external API/service.
     * Enables cross-service data enrichment while keeping services loosely coupled.
     * 
     * @param Collection $localData Local data collection
     * @param array $externalData External service data (array of arrays)
     * @param string $localKey Local collection key to join on
     * @param string $externalKey External data key to join on
     * @param string $mergeAs Key to store merged data under
     * @return Collection Enriched collection
     */
    public function mergeWithExternalData(
        Collection $localData,
        array $externalData,
        string $localKey,
        string $externalKey,
        string $mergeAs
    ): Collection {
        $externalMap = collect($externalData)->keyBy($externalKey);

        return $localData->map(function ($item) use ($externalMap, $localKey, $mergeAs) {
            $key = is_array($item) ? ($item[$localKey] ?? null) : ($item->{$localKey} ?? null);

            if ($key !== null && $externalMap->has($key)) {
                if (is_array($item)) {
                    $item[$mergeAs] = $externalMap->get($key);
                } else {
                    $item->setAttribute($mergeAs, $externalMap->get($key));
                }
            }

            return $item;
        });
    }

    /**
     * Filter a collection using cross-service data.
     * 
     * @param Collection $localData Local data
     * @param array $externalData External service data
     * @param string $localKey Local key
     * @param string $externalKey External key
     * @param Closure $filterCallback Callback to determine if item should be included
     * @return Collection Filtered collection
     */
    public function filterWithExternalData(
        Collection $localData,
        array $externalData,
        string $localKey,
        string $externalKey,
        Closure $filterCallback
    ): Collection {
        $externalMap = collect($externalData)->keyBy($externalKey);

        return $localData->filter(function ($item) use ($externalMap, $localKey, $filterCallback) {
            $key = is_array($item) ? ($item[$localKey] ?? null) : ($item->{$localKey} ?? null);
            $externalItem = $key !== null ? $externalMap->get($key) : null;
            return $filterCallback($item, $externalItem);
        })->values();
    }

    /**
     * Build a dataset from an array (non-database source).
     * Useful for building repository-like interfaces over external APIs.
     * 
     * @param array $data Array of data
     * @param array $params Query parameters (filtering, sorting, pagination)
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function fromArray(array $data, array $params = []): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $collection = collect($data);

        // Apply search
        if (!empty($params['search']) && !empty($this->searchableColumns)) {
            $search = strtolower($params['search']);
            $collection = $collection->filter(function ($item) use ($search) {
                foreach ($this->searchableColumns as $column) {
                    $value = is_array($item) ? ($item[$column] ?? '') : ($item->{$column} ?? '');
                    if (str_contains(strtolower((string) $value), $search)) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        // Apply filters
        if (!empty($params['filters'])) {
            foreach ($params['filters'] as $column => $value) {
                $collection = $collection->filter(function ($item) use ($column, $value) {
                    $itemValue = is_array($item) ? ($item[$column] ?? null) : ($item->{$column} ?? null);
                    return $itemValue === $value;
                })->values();
            }
        }

        // Apply sorting
        if (!empty($params['sort_by'])) {
            $sortBy = $params['sort_by'];
            $sortDir = strtolower($params['sort_dir'] ?? 'asc');
            $collection = $sortDir === 'desc'
                ? $collection->sortByDesc(fn($item) => is_array($item) ? ($item[$sortBy] ?? null) : ($item->{$sortBy} ?? null))->values()
                : $collection->sortBy(fn($item) => is_array($item) ? ($item[$sortBy] ?? null) : ($item->{$sortBy} ?? null))->values();
        }

        // Return all or paginated
        if (isset($params['paginate']) && $params['paginate'] === false) {
            return $collection;
        }

        $perPage = $this->resolvePerPage($params);
        $page = (int) ($params['page'] ?? 1);
        $offset = ($page - 1) * $perPage;
        $total = $collection->count();

        $items = $collection->slice($offset, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $params['page_name'] ?? 'page',
            ]
        );
    }

    // =========================================================================
    // CACHING
    // =========================================================================

    /**
     * Execute a query with caching.
     * 
     * @param string $key Cache key
     * @param Closure $callback Query callback
     * @param int|null $ttl Cache TTL in seconds (null uses default)
     * @return mixed
     */
    public function remember(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        return Cache::remember(
            key: $this->buildCacheKey($key),
            ttl: $ttl ?? $this->cacheTtl,
            callback: $callback
        );
    }

    /**
     * Flush all cached data for this repository.
     * Note: Cache tag flushing requires a driver that supports tags (e.g. Redis, Memcached).
     * File and database drivers do not support tagging and will throw a BadMethodCallException.
     * 
     * @return void
     */
    public function flushCache(): void
    {
        try {
            Cache::tags($this->getCacheTags())->flush();
        } catch (\BadMethodCallException) {
            // Driver does not support tags; flush by key prefix is not universally available,
            // so we simply skip – callers relying on cache invalidation should use a tag-capable driver.
        }
    }

    /**
     * Build a namespaced cache key.
     * 
     * @param string $key
     * @return string
     */
    protected function buildCacheKey(string $key): string
    {
        return sprintf('%s:%s', $this->model->getTable(), $key);
    }

    /**
     * Get cache tags for this repository.
     * Override in concrete repositories to add custom tags.
     * 
     * @return array
     */
    protected function getCacheTags(): array
    {
        return [$this->model->getTable()];
    }

    // =========================================================================
    // PRIVATE QUERY BUILDING HELPERS
    // =========================================================================

    /**
     * Apply conditions to a query builder.
     * 
     * Supports:
     * - Simple [column => value] pairs
     * - [column, operator, value] triplets
     * - Nested conditions using closures
     * - whereIn using arrays as values
     * - whereNull/whereNotNull using null values
     * 
     * @param Builder $query
     * @param array $conditions
     * @return Builder
     */
    protected function applyConditions(Builder $query, array $conditions): Builder
    {
        foreach ($conditions as $key => $value) {
            if (is_array($value) && !is_string($key)) {
                // [column, operator, value] triplet
                if (count($value) === 3) {
                    [$column, $operator, $val] = $value;
                    $query->where($column, $operator, $val);
                } elseif (count($value) === 2) {
                    [$column, $val] = $value;
                    $query->where($column, $val);
                }
            } elseif (is_string($key)) {
                if ($value === null) {
                    $query->whereNull($key);
                } elseif (is_array($value)) {
                    $query->whereIn($key, $value);
                } else {
                    $query->where($key, $value);
                }
            } elseif ($value instanceof Closure) {
                $query->where($value);
            }
        }

        return $query;
    }

    /**
     * Apply advanced filters to a query.
     * Supports operators: =, !=, >, >=, <, <=, like, not_like, in, not_in, between, is_null, is_not_null
     * 
     * @param Builder $query
     * @param array $filters Array of filter definitions
     * @return Builder
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $filter) {
            // Skip if column not in filterable list (when list is defined)
            if (!empty($this->filterableColumns) && !in_array($column, $this->filterableColumns)) {
                continue;
            }

            if (is_array($filter) && isset($filter['operator'])) {
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;

                match ($operator) {
                    '=', '!=', '>', '>=', '<', '<=' => $query->where($column, $operator, $value),
                    'like' => $query->where($column, 'LIKE', '%' . $value . '%'),
                    'starts_with' => $query->where($column, 'LIKE', $value . '%'),
                    'ends_with' => $query->where($column, 'LIKE', '%' . $value),
                    'not_like' => $query->where($column, 'NOT LIKE', '%' . $value . '%'),
                    'in' => $query->whereIn($column, (array) $value),
                    'not_in' => $query->whereNotIn($column, (array) $value),
                    'between' => $query->whereBetween($column, $value),
                    'not_between' => $query->whereNotBetween($column, $value),
                    'is_null' => $query->whereNull($column),
                    'is_not_null' => $query->whereNotNull($column),
                    'date' => $query->whereDate($column, $value),
                    'date_range' => $query->whereBetween(DB::raw("DATE($column)"), $value),
                    default => $query->where($column, $value),
                };
            } else {
                // Simple value filter
                if ($filter === null) {
                    $query->whereNull($column);
                } elseif (is_array($filter)) {
                    $query->whereIn($column, $filter);
                } else {
                    $query->where($column, $filter);
                }
            }
        }

        return $query;
    }

    /**
     * Apply full-text search across searchable columns.
     * 
     * @param Builder $query
     * @param string $search Search term
     * @return Builder
     */
    protected function applySearch(Builder $query, string $search): Builder
    {
        if (empty($this->searchableColumns)) {
            return $query;
        }

        $query->where(function (Builder $q) use ($search) {
            foreach ($this->searchableColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                // Handle dot notation for relation searches
                if (str_contains($column, '.')) {
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $q->{$method}($relation, function (Builder $rq) use ($relationColumn, $search) {
                        $rq->where($relationColumn, 'LIKE', '%' . $search . '%');
                    });
                } else {
                    $q->{$method}($column, 'LIKE', '%' . $search . '%');
                }
            }
        });

        return $query;
    }

    /**
     * Apply sorting to a query.
     * 
     * @param Builder $query
     * @param array $params Query parameters
     * @return Builder
     */
    protected function applySorting(Builder $query, array $params): Builder
    {
        $sortBy = $params['sort_by'] ?? null;
        $sortDir = strtolower($params['sort_dir'] ?? 'asc');

        if ($sortDir !== 'asc' && $sortDir !== 'desc') {
            $sortDir = 'asc';
        }

        if ($sortBy) {
            // Validate sortable columns
            if (!empty($this->sortableColumns) && !in_array($sortBy, $this->sortableColumns)) {
                Log::warning('Attempted to sort by non-sortable column', [
                    'column' => $sortBy,
                    'model' => get_class($this->model),
                ]);
                return $query;
            }

            // Handle dot notation for relation sorting.
            // ⚠ Assumes the foreign key follows the '{relation}_id' convention.
            // Relations with custom foreign keys or many-to-many pivots are not supported here;
            // override applySorting() in the concrete repository for those cases.
            if (str_contains($sortBy, '.')) {
                [$relation, $column] = explode('.', $sortBy, 2);
                $relatedModel = $this->model->{$relation}()->getRelated();
                $query->leftJoin(
                    $relatedModel->getTable(),
                    $relatedModel->getTable() . '.id',
                    '=',
                    $this->model->getTable() . '.' . $relation . '_id'
                )->orderBy($relatedModel->getTable() . '.' . $column, $sortDir)
                 ->select($this->model->getTable() . '.*');
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        }

        return $query;
    }

    /**
     * Resolve the number of records per page from params.
     * 
     * @param array $params
     * @return int
     */
    protected function resolvePerPage(array $params): int
    {
        $perPage = (int) ($params['per_page'] ?? $this->defaultPerPage);
        return min(max(1, $perPage), $this->maxPerPage);
    }

    /**
     * Resolve columns to select from params.
     * 
     * @param array $params
     * @return array
     */
    protected function resolveColumns(array $params): array
    {
        if (isset($params['columns'])) {
            if (is_string($params['columns'])) {
                return array_map('trim', explode(',', $params['columns']));
            }
            return $params['columns'];
        }
        return ['*'];
    }

    /**
     * Resolve relations to eager load from params.
     * 
     * @param array $params
     * @return array
     */
    protected function resolveRelations(array $params): array
    {
        $requested = [];

        if (isset($params['with'])) {
            if (is_string($params['with'])) {
                $requested = array_map('trim', explode(',', $params['with']));
            } else {
                $requested = $params['with'];
            }
        }

        return array_merge($this->defaultRelations, $requested);
    }
}
