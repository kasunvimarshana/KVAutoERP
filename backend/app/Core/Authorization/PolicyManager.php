<?php

namespace App\Core\Authorization;

use App\Models\User;

/**
 * Combines RBAC (Spatie permission check) with ABAC (attribute / tenant isolation).
 */
class PolicyManager
{
    /**
     * Authorise a user to perform $action, optionally on a $resource.
     *
     * @param  array<string,mixed>  $context  Additional attributes for ABAC rules.
     */
    public function authorize(User $user, string $action, mixed $resource = null, array $context = []): bool
    {
        if (!$this->checkRBAC($user, $action)) {
            return false;
        }

        if ($resource !== null) {
            return $this->checkABAC($user, $resource, $context);
        }

        return true;
    }

    protected function checkRBAC(User $user, string $action): bool
    {
        return $user->can($action);
    }

    protected function checkABAC(User $user, mixed $resource, array $context = []): bool
    {
        // Tenant isolation: resource must belong to the same tenant as the user.
        if (isset($resource->tenant_id) && $resource->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Extend here with further attribute-based rules as needed.
        return true;
    }
}
