<?php

declare(strict_types=1);

namespace App\Shared\Base;

use App\Shared\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Collection;

/**
 * Abstract Base Repository.
 *
 * Provides a full, Eloquent-backed implementation of {@see RepositoryInterface}
 * with multi-tenant scoping, flexible pagination, full-text search helpers,
 * soft-delete support, and query filter/sort pipelines.
 *
 * Concrete repositories extend this class and supply the Model class:
 *
 *   class ProductRepository extends BaseRepository
 *   {
 *       protected string $modelClass = Product::class;
 *   }
 *
 * @template TModel of Model
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The Eloquent model class this repository manages.
     * Subclasses MUST set this property.
     *
     * @var class-string<Model>
     */
    protected string $modelClass;

    /** Default number of rows per page when paginating. */
    protected int $defaultPerPage = 15;

    /** Whether to use soft deletes (model must use SoftDeletes trait). */
    protected bool $softDelete = true;

    /** Column used to scope queries to a tenant. */
    protected string $tenantColumn = 'tenant_id';

    /** Tenant ID applied as a global query scope when set via scopeForTenant(). */
    protected ?string $defaultTenant = null;

    // ─────────────────────────────────────────────────────────────────────────
    // RepositoryInterface – primary CRUD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     */
    public function findById(string|int $id): ?array
    {
        /** @var Model|null $record */
        $record = $this->newQuery()->find($id);

        return $record?->toArray();
    }

    /**
     * {@inheritDoc}
     *
     * Special keys inside $filters:
     *  - 'page'     (int)  – overrides $page parameter.
     *  - 'per_page' (int)  – overrides $perPage parameter (0 = all).
     *  - 'search'   (string) – applies LIKE search on searchable columns.
     *  - 'with'     (array)  – eager-load relations.
     */
    public function findAll(
        array $filters = [],
        array $sorts = [],
        int $perPage = 0,
        int $page = 1,
    ): array|LengthAwarePaginator {
        // Allow filter bag to override pagination params.
        $perPage = (int) ($filters['per_page'] ?? $perPage);
        $page    = (int) ($filters['page']     ?? $page);

        $query = $this->newQuery();

        // Eager-load relations if requested.
        if (!empty($filters['with'])) {
            $query->with((array) $filters['with']);
        }

        // Remove meta-keys before applying column filters.
        $columnFilters = array_diff_key($filters, array_flip(['page', 'per_page', 'search', 'with']));

        $query = $this->applyFilters($query, $columnFilters);
        $query = $this->applySorts($query, $sorts);

        if (!empty($filters['search'])) {
            $query = $this->applySearch($query, $filters['search']);
        }

        if ($perPage > 0) {
            return $query->paginate(perPage: $perPage, page: $page);
        }

        return $query->get()->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, array $sorts = []): array
    {
        $query = $this->applyFilters($this->newQuery(), $criteria);
        $query = $this->applySorts($query, $sorts);

        return $query->get()->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): array
    {
        /** @var Model $record */
        $record = $this->newModelInstance();
        $record->fill($data);
        $record->save();

        return $record->fresh()->toArray();
    }

    /**
     * {@inheritDoc}
     *
     * @throws ModelNotFoundException
     */
    public function update(string|int $id, array $data): array
    {
        /** @var Model $record */
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record->fresh()->toArray();
    }

    /**
     * {@inheritDoc}
     *
     * Performs a soft delete when the model uses the SoftDeletes trait and
     * $this->softDelete is true; otherwise performs a hard delete.
     *
     * @throws ModelNotFoundException
     */
    public function delete(string|int $id): bool
    {
        $record = $this->findOrFail($id);

        if ($this->softDelete && $this->usesSoftDeletes()) {
            return (bool) $record->delete();
        }

        return (bool) $record->forceDelete();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RepositoryInterface – tenant scoping
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     */
    public function findByTenant(string $tenantId, array $filters = []): array
    {
        $query = $this->newQuery()->where($this->tenantColumn, $tenantId);
        $query = $this->applyFilters($query, $filters);

        return $query->get()->toArray();
    }

    /**
     * Scope subsequent queries to a specific tenant.
     *
     * Returns a clone of the repository with a global query scope applied.
     *
     * @param  string  $tenantId
     * @return static
     */
    public function scopeForTenant(string $tenantId): static
    {
        $clone                = clone $this;
        $clone->defaultTenant = $tenantId;

        return $clone;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RepositoryInterface – pagination
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Handles:
     *  - Eloquent Builder → calls ->paginate()
     *  - Illuminate Collection → slices in PHP
     *  - Plain array (e.g. API response) → slices in PHP
     *  - Any other iterable → converts to Collection then slices
     */
    public function paginate(mixed $source, int $perPage, int $page): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);
        $page    = max(1, $page);

        if ($source instanceof Builder) {
            return $source->paginate(perPage: $perPage, page: $page);
        }

        // Normalise everything else to a Collection.
        $collection = match (true) {
            $source instanceof Collection => $source,
            is_array($source)             => collect($source),
            $source instanceof \Traversable => collect(iterator_to_array($source)),
            default                         => collect((array) $source),
        };

        $total  = $collection->count();
        $offset = ($page - 1) * $perPage;
        $items  = $collection->slice($offset, $perPage)->values();

        return new ConcretePaginator(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path'     => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RepositoryInterface – search
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Uses a series of LIKE '%query%' OR clauses across all specified fields.
     */
    public function search(string $query, array $fields): array
    {
        $builder = $this->newQuery();
        $builder = $this->applySearch($builder, $query, $fields);

        return $builder->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Soft-delete helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Include soft-deleted records in the next query.
     *
     * @return Builder<Model>
     */
    public function withTrashed(): Builder
    {
        return $this->newQuery()->withTrashed();
    }

    /**
     * Return only soft-deleted records.
     *
     * @return Builder<Model>
     */
    public function onlyTrashed(): Builder
    {
        return $this->newQuery()->onlyTrashed();
    }

    /**
     * Restore a soft-deleted record.
     *
     * @param  string|int  $id
     * @return bool
     */
    public function restore(string|int $id): bool
    {
        $record = $this->withTrashed()->findOrFail($id);

        return (bool) $record->restore();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Query pipeline helpers (protected – available to subclasses)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Apply key=value column filters to an Eloquent Builder.
     *
     * Supports:
     *  - Simple equality:   ['status' => 'active']
     *  - Operator syntax:   ['amount' => ['>=', 100]]
     *  - Array (IN clause): ['id' => [1, 2, 3]]
     *  - NULL check:        ['deleted_at' => null]
     *
     * @param  Builder<Model>        $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Model>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            // Skip non-column meta-keys.
            if (in_array($column, ['with', 'search', 'page', 'per_page'], strict: true)) {
                continue;
            }

            if (is_null($value)) {
                $query->whereNull($column);
                continue;
            }

            if (is_array($value)) {
                // Operator syntax: [$op, $val]
                if (count($value) === 2 && in_array($value[0], ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'], strict: true)) {
                    $query->where($column, $value[0], $value[1]);
                    continue;
                }

                // IN clause
                $query->whereIn($column, $value);
                continue;
            }

            $query->where($column, $value);
        }

        return $query;
    }

    /**
     * Apply sort directives to an Eloquent Builder.
     *
     * Format: ['field' => 'asc|desc']
     * Also accepts: ['sort' => 'field', 'direction' => 'asc']
     *
     * @param  Builder<Model>         $query
     * @param  array<string, string>  $sorts
     * @return Builder<Model>
     */
    protected function applySorts(Builder $query, array $sorts): Builder
    {
        foreach ($sorts as $column => $direction) {
            $safeDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($column, $safeDirection);
        }

        return $query;
    }

    /**
     * Apply a LIKE search across the model's searchable columns.
     *
     * @param  Builder<Model>  $query
     * @param  string          $term
     * @param  array<string>   $fields  Overrides $this->searchableColumns when supplied.
     * @return Builder<Model>
     */
    protected function applySearch(Builder $query, string $term, array $fields = []): Builder
    {
        $columns = $fields ?: ($this->searchableColumns ?? []);

        if (empty($columns)) {
            return $query;
        }

        $sanitized = '%' . addcslashes($term, '%_') . '%';

        $query->where(function (Builder $q) use ($columns, $sanitized): void {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', $sanitized);
            }
        });

        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return a fresh Eloquent query Builder for the managed model.
     *
     * If scopeForTenant() was called, automatically applies the tenant filter.
     *
     * @return Builder<Model>
     */
    protected function newQuery(): Builder
    {
        $query = $this->newModelInstance()->newQuery();

        if (isset($this->defaultTenant)) {
            $query->where($this->tenantColumn, $this->defaultTenant);
        }

        return $query;
    }

    /**
     * Instantiate a fresh (unsaved) model instance.
     *
     * @return Model
     */
    protected function newModelInstance(): Model
    {
        return new $this->modelClass();
    }

    /**
     * Find a record by primary key or throw ModelNotFoundException.
     *
     * @param  string|int  $id
     * @return Model
     *
     * @throws ModelNotFoundException
     */
    protected function findOrFail(string|int $id): Model
    {
        /** @var Model|null $record */
        $record = $this->newQuery()->find($id);

        if ($record === null) {
            throw (new ModelNotFoundException())->setModel($this->modelClass, $id);
        }

        return $record;
    }

    /**
     * Determine whether the managed model uses the SoftDeletes trait.
     *
     * @return bool
     */
    protected function usesSoftDeletes(): bool
    {
        return in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($this->modelClass),
            strict: true,
        );
    }
}
