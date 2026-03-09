<?php

declare(strict_types=1);

namespace Saas\Contracts\DTOs;

/**
 * Encapsulates structured query filter parameters for repository queries.
 *
 * This DTO is immutable by design: all properties are `readonly` and the only
 * way to obtain an instance is via the constructor or {@see fromArray()}.
 *
 * Property semantics mirror common ORM query-builder clauses:
 *
 * ```php
 * $filter = new FilterDTO(
 *     where:         ['status' => 'active', 'tenant_id' => $tenantId],
 *     whereIn:       ['category_id' => [1, 2, 3]],
 *     whereNotIn:    ['status' => ['archived', 'deleted']],
 *     whereBetween:  ['created_at' => ['2024-01-01', '2024-12-31']],
 *     with:          ['supplier', 'warehouse'],
 *     select:        ['id', 'name', 'stock_quantity'],
 *     search:        'blue widget',
 *     searchColumns: ['name', 'sku', 'description'],
 * );
 * ```
 */
final class FilterDTO
{
    /**
     * @param array<string, mixed>         $where          Column-to-value equality conditions.
     * @param array<string, array<mixed>>  $whereIn        Column-to-values IN conditions.
     * @param array<string, array<mixed>>  $whereNotIn     Column-to-values NOT IN conditions.
     * @param array<string, array{0: mixed, 1: mixed}> $whereBetween
     *        Column-to-[min, max] BETWEEN conditions.
     * @param string[]                     $with           Eager-load relationships.
     * @param string[]                     $select         Columns to select; empty = all (`SELECT *`).
     * @param string|null                  $search         Free-text search term.
     * @param string[]|null                $searchColumns  Columns to search against; `null` lets the
     *                                                     repository decide.
     */
    public function __construct(
        public readonly array $where = [],
        public readonly array $whereIn = [],
        public readonly array $whereNotIn = [],
        public readonly array $whereBetween = [],
        public readonly array $with = [],
        public readonly array $select = [],
        public readonly ?string $search = null,
        public readonly ?array $searchColumns = null,
    ) {}

    /**
     * Constructs a `FilterDTO` from a raw associative array.
     *
     * Unknown keys are silently ignored, making this safe to use directly with
     * request query parameters or JSON payloads.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            where: isset($data['where']) && is_array($data['where'])
                ? $data['where']
                : [],
            whereIn: isset($data['where_in']) && is_array($data['where_in'])
                ? $data['where_in']
                : [],
            whereNotIn: isset($data['where_not_in']) && is_array($data['where_not_in'])
                ? $data['where_not_in']
                : [],
            whereBetween: isset($data['where_between']) && is_array($data['where_between'])
                ? $data['where_between']
                : [],
            with: isset($data['with']) && is_array($data['with'])
                ? array_values(array_map('strval', $data['with']))
                : [],
            select: isset($data['select']) && is_array($data['select'])
                ? array_values(array_map('strval', $data['select']))
                : [],
            search: isset($data['search']) ? (string) $data['search'] : null,
            searchColumns: isset($data['search_columns']) && is_array($data['search_columns'])
                ? array_values(array_map('strval', $data['search_columns']))
                : null,
        );
    }
}
