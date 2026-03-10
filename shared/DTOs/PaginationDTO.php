<?php

declare(strict_types=1);

namespace Shared\DTOs;

/**
 * Pagination Data Transfer Object
 * 
 * Strongly-typed DTO for pagination parameters.
 * Ensures consistent pagination across all microservices.
 */
final class PaginationDTO
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $pageName,
        public readonly array $columns,
        public readonly ?string $sortBy,
        public readonly string $sortDir,
        public readonly ?string $search,
        public readonly array $filters,
        public readonly array $relations,
    ) {}

    /**
     * Create a PaginationDTO from a request array.
     * 
     * @param array $data Request data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: max(1, (int) ($data['page'] ?? 1)),
            perPage: min(1000, max(1, (int) ($data['per_page'] ?? 15))),
            pageName: $data['page_name'] ?? 'page',
            columns: isset($data['columns'])
                ? (is_string($data['columns']) ? explode(',', $data['columns']) : $data['columns'])
                : ['*'],
            sortBy: $data['sort_by'] ?? null,
            sortDir: in_array(strtolower($data['sort_dir'] ?? ''), ['asc', 'desc']) ? strtolower($data['sort_dir']) : 'asc',
            search: $data['search'] ?? null,
            filters: $data['filters'] ?? [],
            relations: isset($data['with'])
                ? (is_string($data['with']) ? explode(',', $data['with']) : $data['with'])
                : [],
        );
    }

    /**
     * Convert to array for passing to repository methods.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'page_name' => $this->pageName,
            'columns' => $this->columns,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDir,
            'search' => $this->search,
            'filters' => $this->filters,
            'with' => $this->relations,
        ];
    }
}
