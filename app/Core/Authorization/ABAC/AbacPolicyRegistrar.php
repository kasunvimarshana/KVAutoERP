<?php

declare(strict_types=1);

namespace App\Core\Authorization\ABAC;

use App\Modules\Auth\Domain\Models\User;

/**
 * AbacPolicyRegistrar
 *
 * Centralised place to register all ABAC policies.
 * Called from the AuthServiceProvider.
 */
class AbacPolicyRegistrar
{
    public function __construct(private readonly Abac $abac) {}

    /**
     * Register all platform-wide ABAC policies.
     */
    public function register(): void
    {
        // -----------------------------------------------------------------------
        //  Inventory policies
        // -----------------------------------------------------------------------

        $this->abac->define('inventory.read', function (User $user, array $resource, array $env): bool {
            // Allow all authenticated users within the same tenant
            return (int) $user->tenant_id === (int) ($resource['tenant_id'] ?? $user->tenant_id);
        });

        $this->abac->define('inventory.write', function (User $user, array $resource, array $env): bool {
            // Must belong to same tenant AND have the inventory-manager role or higher
            if ((int) $user->tenant_id !== (int) ($resource['tenant_id'] ?? $user->tenant_id)) {
                return false;
            }

            return $user->hasAnyRole(['super-admin', 'admin', 'inventory-manager']);
        });

        $this->abac->define('inventory.delete', function (User $user, array $resource, array $env): bool {
            // Only admins may hard-delete inventory items
            return $user->hasAnyRole(['super-admin', 'admin']);
        });

        // -----------------------------------------------------------------------
        //  Order policies
        // -----------------------------------------------------------------------

        $this->abac->define('order.create', function (User $user, array $resource, array $env): bool {
            return (int) $user->tenant_id === (int) ($resource['tenant_id'] ?? $user->tenant_id)
                && $user->hasAnyRole(['super-admin', 'admin', 'sales-rep', 'customer']);
        });

        $this->abac->define('order.read', function (User $user, array $resource, array $env): bool {
            $isSameTenant = (int) $user->tenant_id === (int) ($resource['tenant_id'] ?? $user->tenant_id);

            if (! $isSameTenant) {
                return false;
            }

            // Customers can only see their own orders
            if ($user->hasRole('customer')) {
                return (int) ($resource['customer_id'] ?? 0) === $user->id;
            }

            return true;
        });

        $this->abac->define('order.cancel', function (User $user, array $resource, array $env): bool {
            $isSameTenant = (int) $user->tenant_id === (int) ($resource['tenant_id'] ?? $user->tenant_id);

            if (! $isSameTenant) {
                return false;
            }

            // Only admin roles or the owning customer may cancel
            if ($user->hasAnyRole(['super-admin', 'admin'])) {
                return true;
            }

            return (int) ($resource['customer_id'] ?? 0) === $user->id
                && ($resource['status'] ?? '') === 'pending';
        });

        // -----------------------------------------------------------------------
        //  Tenant policies (super-admin only)
        // -----------------------------------------------------------------------

        $this->abac->define('tenant.manage', function (User $user, array $resource, array $env): bool {
            return $user->hasRole('super-admin');
        });
    }
}
