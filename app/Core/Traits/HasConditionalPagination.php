<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

/**
 * HasConditionalPagination
 *
 * Provides conditional pagination for any iterable data source
 * (arrays, Collections, API responses, etc.).
 *
 * Returns paginated results when `$perPage` is provided, or the full
 * dataset (as an array) otherwise. Supports both `page` and `per_page`
 * parameters in a flexible, reusable way.
 */
trait HasConditionalPagination
{
    /**
     * Paginate any iterable data source conditionally.
     *
     * @param  iterable<mixed>  $data     The full dataset to paginate
     * @param  int|null         $perPage  Items per page; null returns all items
     * @param  int              $page     1-based page number
     * @return LengthAwarePaginator|array<mixed>
     */
    public function paginateIterable(
        iterable $data,
        ?int $perPage = null,
        int $page = 1
    ): LengthAwarePaginator|array {
        // Normalise input to a Collection for uniform handling
        $collection = $this->normaliseToCollection($data);

        // Return all items when no per_page is requested
        if ($perPage === null || $perPage <= 0) {
            return $collection->values()->all();
        }

        $page  = max(1, $page);
        $total = $collection->count();

        $items = $collection
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return new Paginator(
            $items->all(),
            $total,
            $perPage,
            $page,
            [
                'path'  => function_exists('app') && app()->bound('request') ? request()->url() : '/',
                'query' => function_exists('app') && app()->bound('request') ? request()->query() : [],
            ]
        );
    }

    /**
     * Resolve pagination parameters from a request or array.
     *
     * Supports both `page`/`per_page` and `page`/`limit` naming conventions.
     *
     * @param  array<string,mixed>|null $params  Defaults to current request query params
     * @return array{per_page: int|null, page: int}
     */
    public function resolvePaginationParams(?array $params = null): array
    {
        $params ??= (function_exists('app') && app()->bound('request') ? request()->query() : []) ?? [];

        // Support both "per_page" and "limit" keys
        $perPage = isset($params['per_page'])
            ? (int) $params['per_page']
            : (isset($params['limit']) ? (int) $params['limit'] : null);

        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;

        return [
            'per_page' => ($perPage !== null && $perPage > 0) ? $perPage : null,
            'page'     => $page,
        ];
    }

    // -------------------------------------------------------------------------
    //  Private helpers
    // -------------------------------------------------------------------------

    /**
     * Normalise any iterable to a Laravel Collection.
     *
     * @param  iterable<mixed> $data
     * @return Collection<int, mixed>
     */
    private function normaliseToCollection(iterable $data): Collection
    {
        if ($data instanceof Collection) {
            return $data;
        }

        if (is_array($data)) {
            return collect($data);
        }

        // Generator, Traversable, etc.
        return collect(iterator_to_array($data, false));
    }
}
