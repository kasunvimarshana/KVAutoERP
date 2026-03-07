<?php

namespace App\Infrastructure\Persistence;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): mixed;

    public function find(int|string $id, array $columns = ['*']): mixed;

    public function findOrFail(int|string $id): mixed;

    public function findByCriteria(array $criteria, array $columns = ['*']): mixed;

    public function create(array $data): mixed;

    public function update(int|string $id, array $data): mixed;

    public function delete(int|string $id): bool;

    public function paginate(int $perPage = 15, array $columns = ['*']): mixed;

    /**
     * Conditionally paginate or return all records.
     *
     * When $params contains 'per_page', the result is paginated.
     * Otherwise all records matching any applied filters/search/sort are returned.
     *
     * Supported $params keys:
     *   - filters        array   Column => value pairs (see filter())
     *   - search         string  Full-text search term
     *   - sort_by        string  Column name to sort by
     *   - sort_direction string  'asc' or 'desc'
     *   - per_page       int     Triggers pagination
     *   - page           int     Page number (used when per_page is set)
     */
    public function paginateOrGet(array $params = []): mixed;

    public function filter(array $filters): static;

    public function search(string $term, array $columns): static;

    public function sort(string $column, string $direction = 'asc'): static;

    public function forTenant(string $tenantId): static;

    public function count(): int;

    public function exists(array $criteria): bool;
}
