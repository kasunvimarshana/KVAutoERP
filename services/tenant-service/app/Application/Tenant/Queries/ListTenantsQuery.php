<?php

declare(strict_types=1);

namespace App\Application\Tenant\Queries;

/**
 * Query: List Tenants.
 *
 * Fetches a paginated, filtered, and sorted list of tenants.
 */
final readonly class ListTenantsQuery
{
    /**
     * @param  array<string, mixed>   $filters         Column/value filter pairs.
     * @param  array<string, string>  $sorts            Field → direction ('asc'|'desc') map.
     * @param  int                    $perPage          Rows per page (0 = return all).
     * @param  int                    $page             Current page (1-indexed).
     * @param  bool                   $includeInactive  When true, includes soft-deleted or inactive tenants.
     */
    public function __construct(
        public array $filters = [],
        public array $sorts = ['created_at' => 'desc'],
        public int $perPage = 15,
        public int $page = 1,
        public bool $includeInactive = false,
    ) {}
}
