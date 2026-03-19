<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\DTOs;

/**
 * Data Transfer Object for query filter, sort, and search parameters.
 *
 * Passed into repository methods to decouple the calling layer from
 * the underlying query builder, keeping controllers and service
 * classes free of query-construction logic.
 */
final class FilterDTO
{
    /**
     * @param  array<string, mixed>                                         $filters  Key/value equality filters (e.g. ['status' => 'active']).
     * @param  array<int, array{field: string, direction: 'asc'|'desc'}>    $sorts    Ordered list of sort directives.
     * @param  string|null                                                   $search   Full-text search term applied across searchable columns.
     * @param  array<string, mixed>                                          $meta     Arbitrary extra parameters (e.g. date ranges, nested filters).
     */
    public function __construct(
        public readonly array $filters = [],
        public readonly array $sorts = [],
        public readonly ?string $search = null,
        public readonly array $meta = [],
    ) {}

    /**
     * Create a FilterDTO from a raw request input array.
     *
     * Expects keys: `filters` (assoc array), `sorts` (array of {field, direction}),
     * `search` (string), `meta` (assoc array).
     *
     * @param  array<string, mixed>  $input  Raw request data.
     * @return self
     */
    public static function fromArray(array $input): self
    {
        return new self(
            filters: is_array($input['filters'] ?? null) ? $input['filters'] : [],
            sorts:   is_array($input['sorts'] ?? null)   ? $input['sorts']   : [],
            search:  isset($input['search']) && $input['search'] !== '' ? (string) $input['search'] : null,
            meta:    is_array($input['meta'] ?? null)    ? $input['meta']    : [],
        );
    }

    /**
     * Return a new FilterDTO with an additional equality filter merged in.
     *
     * @param  string  $field  Column or attribute name.
     * @param  mixed   $value  Filter value.
     * @return self             New immutable instance with the added filter.
     */
    public function withFilter(string $field, mixed $value): self
    {
        return new self(
            filters: array_merge($this->filters, [$field => $value]),
            sorts:   $this->sorts,
            search:  $this->search,
            meta:    $this->meta,
        );
    }

    /**
     * Return a new FilterDTO with an additional sort directive appended.
     *
     * @param  string           $field      Column or attribute name.
     * @param  'asc'|'desc'     $direction  Sort direction.
     * @return self              New immutable instance with the added sort.
     */
    public function withSort(string $field, string $direction = 'asc'): self
    {
        return new self(
            filters: $this->filters,
            sorts:   array_merge($this->sorts, [['field' => $field, 'direction' => $direction]]),
            search:  $this->search,
            meta:    $this->meta,
        );
    }

    /**
     * Determine whether any filters, sorts, or a search term have been set.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->filters) && empty($this->sorts) && $this->search === null;
    }

    /**
     * Serialise to an associative array.
     *
     * @return array{filters: array<string, mixed>, sorts: array<int, array{field: string, direction: string}>, search: string|null, meta: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'filters' => $this->filters,
            'sorts'   => $this->sorts,
            'search'  => $this->search,
            'meta'    => $this->meta,
        ];
    }
}
