<?php

namespace App\Application\Services;

use App\Domain\Auth\Entities\User;
use Illuminate\Support\Facades\Log;

/**
 * Attribute-Based Access Control (ABAC) Service.
 *
 * Evaluates fine-grained access decisions by composing multiple policy checks:
 *   1. TenantPolicy   – resource belongs to the user's tenant
 *   2. OwnershipPolicy – user owns the resource
 *   3. ResourcePolicy  – user's role allows the action on the resource type
 */
class ABACService
{
    /** @var callable[] */
    private array $policies = [];

    public function __construct()
    {
        $this->registerDefaultPolicies();
    }

    // -------------------------------------------------------------------------
    // Public evaluation entry-point
    // -------------------------------------------------------------------------

    /**
     * Evaluate whether $user may perform $action on $resource.
     *
     * $resource should be an array with at minimum:
     *   - 'tenant_id'   (string)
     *   - 'user_id'     (string|int, optional for ownership check)
     *   - 'type'        (string, e.g. 'order', 'inventory')
     *
     * Returns true only when ALL registered policies pass.
     */
    public function evaluate(User $user, string $action, mixed $resource): bool
    {
        $resourceArray = is_array($resource) ? $resource : (array) $resource;

        foreach ($this->policies as $policyName => $policy) {
            $result = $policy($user, $action, $resourceArray);

            if ($result === false) {
                Log::debug("ABAC deny: policy [{$policyName}] rejected [{$action}] for user [{$user->id}]");
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Policy registration
    // -------------------------------------------------------------------------

    public function registerPolicy(string $name, callable $policy): void
    {
        $this->policies[$name] = $policy;
    }

    // -------------------------------------------------------------------------
    // Individual policy checks (also callable directly)
    // -------------------------------------------------------------------------

    /**
     * Resource must belong to the user's tenant.
     */
    public function checkTenantScope(User $user, array $resource): bool
    {
        return isset($resource['tenant_id']) && $resource['tenant_id'] === $user->tenant_id;
    }

    /**
     * Resource must be owned by the user (or user has elevated role).
     */
    public function checkOwnership(User $user, array $resource): bool
    {
        if ($user->hasRole(['super-admin', 'tenant-admin', 'manager'])) {
            return true;
        }

        $ownerId = $resource['user_id'] ?? $resource['owner_id'] ?? $resource['customer_id'] ?? null;
        return $ownerId === null || (string) $ownerId === (string) $user->id;
    }

    /**
     * User's role hierarchy must permit the action on the resource type.
     */
    public function checkRoleHierarchy(User $user, string $action, array $resource): bool
    {
        $resourceType = $resource['type'] ?? '*';
        $permission   = "{$resourceType}.{$action}";

        // super-admin bypasses everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check explicit permission
        if ($user->hasPermissionTo($permission)) {
            return true;
        }

        // Wildcard resource-type permission
        return $user->hasPermissionTo("*.{$action}");
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function registerDefaultPolicies(): void
    {
        $this->registerPolicy('tenant_scope', function (User $user, string $action, array $resource): bool {
            return $this->checkTenantScope($user, $resource);
        });

        $this->registerPolicy('ownership', function (User $user, string $action, array $resource): bool {
            // Only enforce ownership for write/delete actions
            if (in_array($action, ['view', 'list'], true)) {
                return true;
            }
            return $this->checkOwnership($user, $resource);
        });

        $this->registerPolicy('role_hierarchy', function (User $user, string $action, array $resource): bool {
            return $this->checkRoleHierarchy($user, $action, $resource);
        });
    }
}
