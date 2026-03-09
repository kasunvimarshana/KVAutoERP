<?php

declare(strict_types=1);

namespace App\Core\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Repository Contract.
 *
 * Defines the core operations every repository must support,
 * including CRUD, filtering, sorting, and conditional pagination.
 */
interface RepositoryInterface
{
    /**
     * Retrieve all records, optionally paginated.
     *
     * @param  array<string,mixed>  $filters   Key/value filter map
     * @param  array<string,string> $sort      ['column' => 'asc|desc']
     * @param  array<string>        $with      Eager-load relations
     * @param  int|null             $perPage   Items per page; null returns all
     * @param  int                  $page      Current page number
     * @return LengthAwarePaginator|Collection
     */
    public function all(
        array $filters = [],
        array $sort = [],
        array $with = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection;

    /**
     * Find a single record by its primary key.
     *
     * @param  int|string $id
     * @return Model|null
     */
    public function findById(int|string $id): ?Model;

    /**
     * Find a single record by arbitrary criteria.
     *
     * @param  array<string,mixed> $criteria
     * @return Model|null
     */
    public function findBy(array $criteria): ?Model;

    /**
     * Create a new record.
     *
     * @param  array<string,mixed> $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing record by primary key.
     *
     * @param  int|string          $id
     * @param  array<string,mixed> $data
     * @return Model
     */
    public function update(int|string $id, array $data): Model;

    /**
     * Delete a record by primary key.
     *
     * @param  int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Search records using a search term across searchable columns.
     *
     * @param  string               $term
     * @param  array<string>        $columns
     * @param  array<string,mixed>  $filters
     * @param  array<string,string> $sort
     * @param  array<string>        $with
     * @param  int|null             $perPage
     * @param  int                  $page
     * @return LengthAwarePaginator|Collection
     */
    public function search(
        string $term,
        array $columns = [],
        array $filters = [],
        array $sort = [],
        array $with = [],
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|Collection;

    /**
     * Paginate any iterable data source (array, Collection, API response, etc.).
     *
     * Returns paginated results when $perPage is provided, otherwise the full set.
     *
     * @param  iterable<mixed>  $data
     * @param  int|null         $perPage
     * @param  int              $page
     * @return LengthAwarePaginator|array<mixed>
     */
    public function paginateData(
        iterable $data,
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|array;
}
