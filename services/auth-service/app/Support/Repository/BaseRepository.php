<?php

declare(strict_types=1);

namespace App\Support\Repository;

use App\Contracts\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as ManualPaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Abstract base repository with full CRUD, dynamic filtering, sorting,
 * eager-loading, field selection, and conditional pagination.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /** @var class-string<Model> */
    protected string $model;

    public function __construct()
    {
        if (! isset($this->model)) {
            throw new InvalidArgumentException(static::class . ' must declare a $model property.');
        }
    }

    // -------------------------------------------------------------------------
    // Core CRUD
    // -------------------------------------------------------------------------

    public function findById(string|int $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    public function update(string|int $id, array $data): ?Model
    {
        $model = $this->findById($id);

        if ($model === null) {
            return null;
        }

        $model->fill($data)->save();

        return $model->fresh();
    }

    public function delete(string|int $id): bool
    {
        $model = $this->findById($id);

        if ($model === null) {
            return false;
        }

        return (bool) $model->delete();
    }

    public function exists(array $conditions): bool
    {
        return $this->newQuery()->where($conditions)->exists();
    }

    public function count(array $filters = []): int
    {
        $query = $this->newQuery();
        $this->applyWhereFilters($query, $filters);

        return $query->count();
    }

    // -------------------------------------------------------------------------
    // findAll with dynamic options
    // -------------------------------------------------------------------------

    /**
     * @param array{
     *     where?: array<string, mixed>,
     *     whereIn?: array<string, list<mixed>>,
     *     whereBetween?: array<string, array{0: mixed, 1: mixed}>,
     *     search?: array{term: string, columns: list<string>},
     *     with?: list<string>,
     *     select?: list<string>,
     *     sortBy?: string,
     *     sortDirection?: 'asc'|'desc',
     *     perPage?: int,
     *     page?: int,
     * } $filters
     * @param array<string, mixed> $options  (merged into $filters for BC)
     */
    public function findAll(array $filters = [], array $options = []): Collection|LengthAwarePaginator
    {
        $params = array_merge($filters, $options);
        $query  = $this->newQuery();

        // Field selection
        if (! empty($params['select'])) {
            $query->select($params['select']);
        }

        // Eager loading
        if (! empty($params['with'])) {
            $query->with($params['with']);
        }

        // Simple where clauses
        $this->applyWhereFilters($query, $params['where'] ?? []);

        // whereIn clauses
        foreach ($params['whereIn'] ?? [] as $column => $values) {
            $query->whereIn($column, $values);
        }

        // whereBetween clauses
        foreach ($params['whereBetween'] ?? [] as $column => $range) {
            $query->whereBetween($column, $range);
        }

        // Full-text-style search across specified columns
        if (! empty($params['search']['term']) && ! empty($params['search']['columns'])) {
            $term    = $params['search']['term'];
            $columns = $params['search']['columns'];

            $query->where(function (Builder $q) use ($term, $columns): void {
                foreach ($columns as $i => $column) {
                    $method = $i === 0 ? 'where' : 'orWhere';
                    $q->{$method}($column, 'LIKE', "%{$term}%");
                }
            });
        }

        // Sorting
        $sortBy        = $params['sortBy']        ?? 'created_at';
        $sortDirection = $params['sortDirection']  ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = (int) ($params['perPage'] ?? 0);

        if ($perPage === 0) {
            return $query->get();
        }

        $page = (int) ($params['page'] ?? 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    // -------------------------------------------------------------------------
    // applyPagination – works on queries, arrays, and collections
    // -------------------------------------------------------------------------

    /**
     * Apply pagination to a query, array, or collection.
     *
     * @param Builder|array<int, mixed>|Collection $source
     */
    public function applyPagination(mixed $source, PaginationDTO $pagination): Collection|LengthAwarePaginator
    {
        if ($pagination->perPage === 0) {
            if ($source instanceof Builder) {
                return $source->get();
            }

            if (is_array($source)) {
                return collect($source);
            }

            return $source;
        }

        if ($source instanceof Builder) {
            return $source->paginate($pagination->perPage, ['*'], 'page', $pagination->page);
        }

        // Manual paginator for arrays / collections
        $items = is_array($source) ? collect($source) : $source;
        $total = $items->count();
        $slice = $items->forPage($pagination->page, $pagination->perPage)->values();

        return new ManualPaginator(
            $slice,
            $total,
            $pagination->perPage,
            $pagination->page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function newQuery(): Builder
    {
        /** @var Model $instance */
        $instance = new $this->model();

        return $instance->newQuery();
    }

    /** @param array<string, mixed> $filters */
    private function applyWhereFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }
    }
}
