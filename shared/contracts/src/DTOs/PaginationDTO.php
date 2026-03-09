<?php

declare(strict_types=1);

namespace Saas\Contracts\DTOs;

/**
 * Encapsulates pagination and sorting parameters for list/search requests.
 *
 * This DTO is immutable by design: all properties are `readonly` and the only
 * way to obtain an instance is via the constructor or {@see fromArray()}.
 *
 * @phpstan-type SortDirection 'asc'|'desc'
 */
final class PaginationDTO
{
    /**
     * @param int         $page          1-based page number.
     * @param int         $perPage       Records per page; set to `0` to retrieve all records
     *                                   (see {@see shouldPaginate()}).
     * @param string|null $sortBy        Column or field name to sort by; `null` uses the
     *                                   repository's default ordering.
     * @param string      $sortDirection Sort direction — `'asc'` or `'desc'`.
     * @param array<string, mixed> $filters Key-value equality filters to apply alongside pagination.
     * @param string|null $search        Free-text search term; how it is applied depends on the
     *                                   repository implementation.
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $sortBy = null,
        public readonly string $sortDirection = 'asc',
        public readonly array $filters = [],
        public readonly ?string $search = null,
    ) {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('PaginationDTO::$page must be >= 1.');
        }

        if ($this->perPage < 0) {
            throw new \InvalidArgumentException('PaginationDTO::$perPage must be >= 0.');
        }

        if (!in_array($this->sortDirection, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException(
                "PaginationDTO::\$sortDirection must be 'asc' or 'desc', got '{$this->sortDirection}'."
            );
        }
    }

    /**
     * Constructs a `PaginationDTO` from a raw associative array.
     *
     * Unknown keys are silently ignored, making this safe to use directly with
     * request query parameters.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 15,
            sortBy: isset($data['sort_by']) ? (string) $data['sort_by'] : null,
            sortDirection: isset($data['sort_direction']) ? (string) $data['sort_direction'] : 'asc',
            filters: isset($data['filters']) && is_array($data['filters']) ? $data['filters'] : [],
            search: isset($data['search']) ? (string) $data['search'] : null,
        );
    }

    /**
     * Returns `true` when pagination is active (i.e. `$perPage > 0`).
     *
     * Passing `perPage = 0` signals to the repository that it should return
     * all matching records without pagination.
     */
    public function shouldPaginate(): bool
    {
        return $this->perPage > 0;
    }
}
