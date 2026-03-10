<?php

declare(strict_types=1);

namespace Shared\BaseRepository;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Base Repository Interface
 * 
 * Defines the contract for all repository implementations.
 * Following Clean Architecture - Application layer depends on this interface,
 * not on concrete implementations.
 */
interface BaseRepositoryInterface
{
    public function findById(int|string $id, array $columns = ['*'], array $relations = [], bool $fail = false): ?Model;
    public function findBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): ?Model;
    public function findAllBy(string $column, mixed $value, array $columns = ['*'], array $relations = []): Collection;
    public function findWhere(array $conditions, array $columns = ['*'], array $relations = []): Collection;
    public function firstWhere(array $conditions, array $columns = ['*'], array $relations = []): ?Model;
    public function all(array $columns = ['*'], array $relations = [], array $orderBy = []): Collection;
    public function create(array $data): Model;
    public function createMany(array $data, int $chunkSize = 500): bool;
    public function update(int|string $id, array $data): ?Model;
    public function updateWhere(array $conditions, array $data): int;
    public function updateOrCreate(array $conditions, array $data): Model;
    public function delete(int|string $id, bool $force = false): bool;
    public function deleteWhere(array $conditions, bool $force = false): int;
    public function restore(int|string $id): bool;
    public function paginate(array $params = [], array $additionalConditions = [], ?Closure $queryCallback = null): LengthAwarePaginator;
    public function exists(array $conditions): bool;
    public function count(array $conditions = []): int;
    public function max(string $column, array $conditions = []): mixed;
    public function min(string $column, array $conditions = []): mixed;
    public function sum(string $column, array $conditions = []): float|int;
    public function avg(string $column, array $conditions = []): ?float;
    public function pluck(string $valueColumn, ?string $keyColumn = null, array $conditions = []);
    public function mergeWithExternalData(Collection $localData, array $externalData, string $localKey, string $externalKey, string $mergeAs): Collection;
    public function filterWithExternalData(Collection $localData, array $externalData, string $localKey, string $externalKey, Closure $filterCallback): Collection;
    public function fromArray(array $data, array $params = []): LengthAwarePaginator|SupportCollection;
    public function remember(string $key, Closure $callback, ?int $ttl = null): mixed;
    public function flushCache(): void;
    public function getModel(): Model;
}
