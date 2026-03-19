<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Repository;

use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Base repository contract defining standard CRUD and pagination operations.
 *
 * All concrete repositories must implement this interface to ensure
 * consistent data access patterns across microservices.
 */
interface RepositoryInterface
{
    /**
     * Find a single record by its primary identifier.
     *
     * @param  string|int  $id  The record's primary key.
     * @return object|null      The found record or null if not found.
     */
    public function findById(string|int $id): ?object;

    /**
     * Retrieve all records, optionally filtered and sorted.
     *
     * @param  FilterDTO|null  $filter  Optional filter/sort criteria.
     * @return array<int, object>        A flat list of matching records.
     */
    public function findAll(?FilterDTO $filter = null): array;

    /**
     * Persist a new record from the supplied attribute map.
     *
     * @param  array<string, mixed>  $data  Attribute key/value pairs.
     * @return object                        The newly created record.
     */
    public function create(array $data): object;

    /**
     * Update an existing record by its primary identifier.
     *
     * @param  string|int            $id    The record's primary key.
     * @param  array<string, mixed>  $data  Attribute key/value pairs to update.
     * @return object                        The updated record.
     */
    public function update(string|int $id, array $data): object;

    /**
     * Remove a record permanently by its primary identifier.
     *
     * @param  string|int  $id  The record's primary key.
     * @return bool              True on success, false if the record was not found.
     */
    public function delete(string|int $id): bool;

    /**
     * Return a paginated result set.
     *
     * @param  int              $page      1-based page number.
     * @param  int              $perPage   Number of records per page.
     * @param  FilterDTO|null   $filter    Optional filter/sort criteria.
     * @return PaginationDTO               Pagination envelope containing items and metadata.
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): PaginationDTO;
}
