<?php

declare(strict_types=1);

namespace App\Contracts;

interface PolicyServiceContract
{
    /**
     * Evaluate whether the given subject is allowed to perform $action on $resource.
     *
     * @param  array<string, mixed>  $subject     JWT claims or attribute map (roles, tenant_id, …)
     * @param  string                $action      e.g. "users:read", "products:delete"
     * @param  array<string, mixed>  $resource    Attributes of the target resource (entity_type, entity_id, …)
     * @param  array<string, mixed>  $environment Contextual attributes (ip_address, request_time, …)
     */
    public function evaluate(
        array  $subject,
        string $action,
        array  $resource     = [],
        array  $environment  = [],
    ): bool;

    /** Create a new ABAC policy. */
    public function create(array $data): array;

    /** Update an existing policy. */
    public function update(string $policyId, array $data): array;

    /** Delete a policy (soft-delete). */
    public function delete(string $policyId): void;

    /** Find a policy by its ID. */
    public function findById(string $policyId): ?array;

    /**
     * List policies for a tenant (or global if tenantId is empty).
     *
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(string $tenantId, array $filters = [], int $perPage = 20): array;
}
