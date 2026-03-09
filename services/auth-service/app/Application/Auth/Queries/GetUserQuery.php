<?php

declare(strict_types=1);

namespace App\Application\Auth\Queries;

/**
 * Query: Get User.
 *
 * Used to retrieve a single user by ID within a tenant scope.
 */
final readonly class GetUserQuery
{
    /**
     * @param  string  $userId    Target user's UUID.
     * @param  string  $tenantId  Tenant context for the query.
     */
    public function __construct(
        public string $userId,
        public string $tenantId,
    ) {}
}
