<?php

declare(strict_types=1);

namespace Saas\Contracts\Repository;

/**
 * Generic CRUD + pagination contract for any aggregate repository.
 *
 * Service-specific repositories should extend this interface with strongly-typed
 * variants (replacing `mixed` with concrete return types) where the additional
 * type safety is worth the overhead.
 */
interface RepositoryInterface
{
    /**
     * Finds a single record by its primary key.
     *
     * @param string|int $id The primary key value.
     *
     * @return mixed The matching entity/model, or `null` when not found.
     */
    public function findById(string|int $id): mixed;

    /**
     * Returns a collection of records optionally filtered and ordered.
     *
     * @param array<string, mixed> $filters Key-value pairs applied as equality conditions
     *                                      unless the implementation supports richer syntax.
     * @param array<string, mixed> $options Driver-specific options such as `orderBy`, `limit`, `with`.
     *
     * @return mixed An iterable collection of entities/models.
     */
    public function findAll(array $filters = [], array $options = []): mixed;

    /**
     * Persists a new record and returns the created entity.
     *
     * @param array<string, mixed> $data Attribute map for the new record.
     *
     * @return mixed The newly created entity/model.
     */
    public function create(array $data): mixed;

    /**
     * Updates an existing record and returns the updated entity.
     *
     * @param string|int           $id   Primary key of the record to update.
     * @param array<string, mixed> $data Partial or full attribute map to apply.
     *
     * @return mixed The updated entity/model.
     */
    public function update(string|int $id, array $data): mixed;

    /**
     * Removes a record from persistence.
     *
     * @param string|int $id Primary key of the record to delete.
     *
     * @return bool `true` on success, `false` when the record was not found or
     *              could not be deleted.
     */
    public function delete(string|int $id): bool;

    /**
     * Returns a paginated result set.
     *
     * @param array<string, mixed> $filters  Key-value pairs applied as equality conditions.
     * @param int                  $page     1-based page number.
     * @param int                  $perPage  Number of records per page.
     *
     * @return mixed A paginator instance or an array containing `data` and `meta` keys.
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): mixed;
}
