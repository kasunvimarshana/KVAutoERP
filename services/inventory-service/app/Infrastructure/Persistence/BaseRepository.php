<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected Builder $query;
    protected array $searchableColumns = [];
    protected array $filterableColumns = [];
    protected array $sortableColumns = [];
    protected ?string $tenantId = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resetQuery();
    }

    // ─── Internal helpers ────────────────────────────────────────────────────

    protected function resetQuery(): static
    {
        $this->query = $this->model->newQuery();

        if ($this->tenantId !== null) {
            $this->query->where('tenant_id', $this->tenantId);
        }

        return $this;
    }

    // ─── BaseRepositoryInterface implementation ───────────────────────────────

    public function all(array $columns = ['*']): Collection
    {
        $result = $this->query->get($columns);
        $this->resetQuery();

        return $result;
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        $result = $this->query->find($id, $columns);
        $this->resetQuery();

        return $result;
    }

    public function findOrFail(int|string $id): Model
    {
        $result = $this->query->findOrFail($id);
        $this->resetQuery();

        return $result;
    }

    public function findByCriteria(array $criteria, array $columns = ['*']): Collection
    {
        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $this->query->whereIn($key, $value);
            } else {
                $this->query->where($key, $value);
            }
        }

        $result = $this->query->get($columns);
        $this->resetQuery();

        return $result;
    }

    public function create(array $data): Model
    {
        if ($this->tenantId !== null && ! isset($data['tenant_id'])) {
            $data['tenant_id'] = $this->tenantId;
        }

        $result = $this->model->create($data);
        $this->resetQuery();

        return $result;
    }

    public function update(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        $this->resetQuery();

        return $record->fresh();
    }

    public function delete(int|string $id): bool
    {
        $record = $this->findOrFail($id);
        $result = (bool) $record->delete();
        $this->resetQuery();

        return $result;
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage, $columns);
        $this->resetQuery();

        return $result;
    }

    /**
     * Conditionally paginate or return all records.
     *
     * Steps (in order):
     *  1. Apply filters when 'filters' key exists in $params.
     *  2. Apply search  when 'search'  key exists in $params and searchableColumns are defined.
     *  3. Apply sort    when 'sort_by' key exists in $params (falls back to created_at desc).
     *  4. Paginate      when 'per_page' key exists in $params (honours 'page' key).
     *  5. Return all    when 'per_page' key is absent.
     */
    public function paginateOrGet(array $params = []): Collection|LengthAwarePaginator
    {
        // 1. Filters
        if (! empty($params['filters']) && is_array($params['filters'])) {
            $this->filter($params['filters']);
        }

        // 2. Search
        if (! empty($params['search']) && ! empty($this->searchableColumns)) {
            $this->search((string) $params['search'], $this->searchableColumns);
        }

        // 3. Sort
        $sortColumn    = $params['sort_by'] ?? 'created_at';
        $sortDirection = isset($params['sort_direction']) ? strtolower((string) $params['sort_direction']) : 'desc';

        $allowedSortColumns = array_merge($this->sortableColumns, ['created_at', 'updated_at', 'id']);
        if (in_array($sortColumn, $allowedSortColumns, true)) {
            $this->sort($sortColumn, $sortDirection);
        }

        // 4 & 5. Paginate or return all
        if (array_key_exists('per_page', $params)) {
            $perPage = max(1, (int) $params['per_page']);
            $page    = isset($params['page']) ? max(1, (int) $params['page']) : 1;
            $result  = $this->query->paginate($perPage, ['*'], 'page', $page);
        } else {
            $result = $this->query->get();
        }

        $this->resetQuery();

        return $result;
    }

    public function filter(array $filters): static
    {
        foreach ($filters as $column => $value) {
            if (! in_array($column, $this->filterableColumns, true)) {
                continue;
            }

            if (is_array($value)) {
                if (isset($value['from']) || isset($value['to'])) {
                    // Range filter
                    if (isset($value['from'])) {
                        $this->query->where($column, '>=', $value['from']);
                    }
                    if (isset($value['to'])) {
                        $this->query->where($column, '<=', $value['to']);
                    }
                } elseif (isset($value['operator'], $value['value'])) {
                    // Custom operator filter, e.g. ['operator' => '>=', 'value' => 10]
                    $this->query->where($column, $value['operator'], $value['value']);
                } else {
                    // IN filter
                    $this->query->whereIn($column, $value);
                }
            } elseif ($value === 'null') {
                $this->query->whereNull($column);
            } elseif ($value === 'not_null') {
                $this->query->whereNotNull($column);
            } else {
                $this->query->where($column, $value);
            }
        }

        return $this;
    }

    public function search(string $term, array $columns): static
    {
        // Only search columns that are declared as searchable
        $searchColumns = ! empty($this->searchableColumns)
            ? array_intersect($columns, $this->searchableColumns)
            : $columns;

        if (empty($searchColumns)) {
            $searchColumns = $columns;
        }

        $this->query->where(function (Builder $query) use ($term, $searchColumns): void {
            foreach (array_values($searchColumns) as $i => $column) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $query->$method($column, 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function sort(string $column, string $direction = 'asc'): static
    {
        $safeDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $this->query->orderBy($column, $safeDirection);

        return $this;
    }

    public function forTenant(string $tenantId): static
    {
        $this->tenantId = $tenantId;
        $this->query->where('tenant_id', $tenantId);

        return $this;
    }

    public function count(): int
    {
        $result = $this->query->count();
        $this->resetQuery();

        return $result;
    }

    public function exists(array $criteria): bool
    {
        foreach ($criteria as $key => $value) {
            $this->query->where($key, $value);
        }

        $result = $this->query->exists();
        $this->resetQuery();

        return $result;
    }

    // ─── Extended helpers (not in interface) ─────────────────────────────────

    public function with(array|string $relations): static
    {
        $this->query->with($relations);

        return $this;
    }

    public function withTrashed(): static
    {
        if (method_exists($this->model, 'withTrashed')) {
            $this->query->withTrashed();
        }

        return $this;
    }

    public function latest(string $column = 'created_at'): static
    {
        $this->query->latest($column);

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $this->query->whereIn($column, $values);

        return $this;
    }

    public function firstWhere(array $criteria): ?Model
    {
        $result = $this->query->where($criteria)->first();
        $this->resetQuery();

        return $result;
    }

    public function updateOrCreate(array $criteria, array $data): Model
    {
        if ($this->tenantId !== null && ! isset($data['tenant_id'])) {
            $data['tenant_id']     = $this->tenantId;
            $criteria['tenant_id'] = $this->tenantId;
        }

        $result = $this->model->updateOrCreate($criteria, $data);
        $this->resetQuery();

        return $result;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
