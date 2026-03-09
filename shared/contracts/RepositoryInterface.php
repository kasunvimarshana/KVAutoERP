<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Base Repository Contract.
 *
 * Defines the standard persistence interface for all domain repositories
 * in the KV_SAAS multi-tenant Inventory Management System.
 *
 * @template TModel of array
 */
interface RepositoryInterface
{
    /**
     * Find a single record by its primary key.
     *
     * @param  string|int  $id  Primary key value.
     * @return array|null       Hydrated record or null if not found.
     */
    public function findById(string|int $id): ?array;

    /**
     * Retrieve all records, optionally filtered, sorted, and paginated.
     *
     * @param  array<string, mixed>   $filters  Key/value pairs to filter on.
     * @param  array<string, string>  $sorts    Field → direction ('asc'|'desc') pairs.
     * @param  int                    $perPage  Rows per page; 0 means return all.
     * @param  int                    $page     Current page (1-indexed).
     * @return array|LengthAwarePaginator       Full result set or paginator.
     */
    public function findAll(
        array $filters = [],
        array $sorts = [],
        int $perPage = 0,
        int $page = 1,
    ): array|LengthAwarePaginator;

    /**
     * Find records matching all supplied criteria.
     *
     * @param  array<string, mixed>   $criteria  Exact-match field/value pairs.
     * @param  array<string, string>  $sorts     Optional sort directives.
     * @return array<int, array>                 Matching records.
     */
    public function findBy(array $criteria, array $sorts = []): array;

    /**
     * Persist a new record.
     *
     * @param  array<string, mixed>  $data  Column/value map.
     * @return array                        Freshly created record.
     */
    public function create(array $data): array;

    /**
     * Update an existing record by primary key.
     *
     * @param  string|int            $id    Primary key of the record to update.
     * @param  array<string, mixed>  $data  Fields to update.
     * @return array                        Updated record.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(string|int $id, array $data): array;

    /**
     * Delete a record by primary key (soft or hard delete depending on model).
     *
     * @param  string|int  $id  Primary key of the record to delete.
     * @return bool             True on success.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(string|int $id): bool;

    /**
     * Return all records belonging to a specific tenant.
     *
     * @param  string                 $tenantId  Tenant identifier.
     * @param  array<string, mixed>   $filters   Additional filters.
     * @return array<int, array>                 Matching records.
     */
    public function findByTenant(string $tenantId, array $filters = []): array;

    /**
     * Paginate any iterable source (Builder, Collection, plain array, or
     * pre-fetched API response array) into a LengthAwarePaginator.
     *
     * @param  mixed  $source   Any iterable or Eloquent Builder instance.
     * @param  int    $perPage  Rows per page.
     * @param  int    $page     Current page (1-indexed).
     * @return LengthAwarePaginator
     */
    public function paginate(mixed $source, int $perPage, int $page): LengthAwarePaginator;

    /**
     * Perform a full-text-style search across the specified fields.
     *
     * @param  string        $query   The search term.
     * @param  array<string> $fields  List of column names to search.
     * @return array<int, array>      Matching records.
     */
    public function search(string $query, array $fields): array;
}
