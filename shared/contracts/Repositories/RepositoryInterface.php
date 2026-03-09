<?php

declare(strict_types=1);

namespace Shared\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Repository Contract.
 *
 * Defines the contract for all repository implementations in the system.
 * Supports CRUD, filtering, searching, sorting, and conditional pagination.
 */
interface RepositoryInterface
{
    /**
     * Retrieve all records with optional filters, sorting, and conditional pagination.
     *
     * Returns paginated results when 'per_page' parameter is present in $params,
     * otherwise returns all matching records.
     *
     * @param array<string, mixed> $params Query parameters (filters, search, sort, page, per_page)
     * @return LengthAwarePaginator|Collection<int, Model>
     */
    public function all(array $params = []): LengthAwarePaginator|Collection;

    /**
     * Find a single record by its primary key.
     *
     * @param int|string $id
     * @return Model|null
     */
    public function find(int|string $id): ?Model;

    /**
     * Find a record by its primary key or throw an exception.
     *
     * @param int|string $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int|string $id): Model;

    /**
     * Find records matching given criteria.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $params Additional query parameters
     * @return LengthAwarePaginator|Collection<int, Model>
     */
    public function findBy(array $criteria, array $params = []): LengthAwarePaginator|Collection;

    /**
     * Find the first record matching given criteria.
     *
     * @param array<string, mixed> $criteria
     * @return Model|null
     */
    public function findOneBy(array $criteria): ?Model;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return Model
     */
    public function update(int|string $id, array $data): Model;

    /**
     * Delete a record by its primary key.
     *
     * @param int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Check if a record exists by its primary key.
     *
     * @param int|string $id
     * @return bool
     */
    public function exists(int|string $id): bool;

    /**
     * Count records matching the given criteria.
     *
     * @param array<string, mixed> $criteria
     * @return int
     */
    public function count(array $criteria = []): int;
}
