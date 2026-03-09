<?php

declare(strict_types=1);

namespace App\Application\Auth\Queries;

/**
 * Query: Get Users (paginated list).
 *
 * Encapsulates all search/sort/pagination parameters for the user listing.
 */
final readonly class GetUsersQuery
{
    /**
     * @param  string                 $tenantId  Tenant scope — only users belonging to this tenant are returned.
     * @param  array<string, mixed>   $filters   Key/value column filters (e.g. ['is_active' => true]).
     * @param  array<string, string>  $sorts     Sort directives (e.g. ['name' => 'asc']).
     * @param  int                    $perPage   Number of results per page.
     * @param  int                    $page      1-based page number.
     */
    public function __construct(
        public string $tenantId,
        public array $filters = [],
        public array $sorts = ['created_at' => 'desc'],
        public int $perPage = 15,
        public int $page = 1,
    ) {}
}
