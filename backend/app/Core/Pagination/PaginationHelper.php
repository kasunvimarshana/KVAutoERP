<?php

namespace App\Core\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Conditional pagination helper.
 *
 * - If `per_page` is present in $params  → return paginated results with meta + links.
 * - If `per_page` is absent              → return all results.
 *
 * Works with Eloquent Builder, Collection, and plain arrays.
 */
class PaginationHelper
{
    public static function paginate(
        Builder|Collection|array $source,
        array $params = []
    ): array {
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : null;
        $page    = isset($params['page'])     ? (int) $params['page']     : 1;

        if ($perPage !== null) {
            return self::paginateSource($source, $perPage, $page);
        }

        return self::fetchAll($source);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private static function paginateSource(
        Builder|Collection|array $source,
        int $perPage,
        int $page
    ): array {
        if ($source instanceof Builder) {
            $paginated = $source->paginate($perPage, ['*'], 'page', $page);

            return [
                'data'  => $paginated->items(),
                'meta'  => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                    'from'         => $paginated->firstItem(),
                    'to'           => $paginated->lastItem(),
                ],
                'links' => [
                    'first' => $paginated->url(1),
                    'last'  => $paginated->url($paginated->lastPage()),
                    'prev'  => $paginated->previousPageUrl(),
                    'next'  => $paginated->nextPageUrl(),
                ],
            ];
        }

        if ($source instanceof Collection) {
            $total     = $source->count();
            $items     = $source->forPage($page, $perPage)->values();
            $paginator = new LengthAwarePaginator($items, $total, $perPage, $page);

            return [
                'data'  => $items->toArray(),
                'meta'  => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last'  => $paginator->url($paginator->lastPage()),
                    'prev'  => $paginator->previousPageUrl(),
                    'next'  => $paginator->nextPageUrl(),
                ],
            ];
        }

        // Plain array
        return self::paginateSource(collect($source), $perPage, $page);
    }

    private static function fetchAll(Builder|Collection|array $source): array
    {
        if ($source instanceof Builder) {
            $items = $source->get();

            return [
                'data' => $items->toArray(),
                'meta' => ['total' => $items->count()],
            ];
        }

        if ($source instanceof Collection) {
            return [
                'data' => $source->values()->toArray(),
                'meta' => ['total' => $source->count()],
            ];
        }

        // Plain array
        return [
            'data' => array_values($source),
            'meta' => ['total' => count($source)],
        ];
    }
}
