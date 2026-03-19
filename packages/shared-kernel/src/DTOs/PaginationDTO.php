<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\DTOs;

/**
 * Data Transfer Object carrying pagination metadata for list responses.
 *
 * Returned by repository paginate methods and embedded in ApiResponseDTO
 * under the `meta.pagination` key.
 */
final class PaginationDTO
{
    /**
     * @param  int                  $page      Current page number (1-based).
     * @param  int                  $perPage   Number of records requested per page.
     * @param  int                  $total     Total number of records across all pages.
     * @param  int                  $lastPage  Number of the final page.
     * @param  int                  $from      1-based index of the first record on this page.
     * @param  int                  $to        1-based index of the last record on this page.
     * @param  array<int, mixed>    $items     The records on the current page.
     */
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage,
        public readonly int $from,
        public readonly int $to,
        public readonly array $items = [],
    ) {}

    /**
     * Create a PaginationDTO from a raw paginated query result.
     *
     * @param  int                $page     Current page.
     * @param  int                $perPage  Records per page.
     * @param  int                $total    Total record count.
     * @param  array<int, mixed>  $items    Records on the current page.
     * @return self
     */
    public static function fromRaw(int $page, int $perPage, int $total, array $items = []): self
    {
        $lastPage = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
        $lastPage = max(1, $lastPage);

        $from = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $to   = min($from + count($items) - 1, $total);
        $to   = $total > 0 ? $to : 0;

        return new self(
            page: $page,
            perPage: $perPage,
            total: $total,
            lastPage: $lastPage,
            from: $from,
            to: $to,
            items: $items,
        );
    }

    /**
     * Determine whether there is a subsequent page.
     *
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->page < $this->lastPage;
    }

    /**
     * Determine whether there is a preceding page.
     *
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    /**
     * Serialise the pagination metadata to an associative array.
     *
     * The `items` field is intentionally excluded here; callers should
     * place items under the `data` key of the API response envelope.
     *
     * @return array{
     *     page: int,
     *     per_page: int,
     *     total: int,
     *     last_page: int,
     *     from: int,
     *     to: int,
     *     has_next_page: bool,
     *     has_previous_page: bool,
     * }
     */
    public function toArray(): array
    {
        return [
            'page'              => $this->page,
            'per_page'          => $this->perPage,
            'total'             => $this->total,
            'last_page'         => $this->lastPage,
            'from'              => $this->from,
            'to'                => $this->to,
            'has_next_page'     => $this->hasNextPage(),
            'has_previous_page' => $this->hasPreviousPage(),
        ];
    }
}
