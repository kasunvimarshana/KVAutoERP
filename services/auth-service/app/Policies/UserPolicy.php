<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Run before any policy check. Admins within the same tenant bypass all checks.
     */
    public function before(User $authUser, string $ability): ?bool
    {
        if ($authUser->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models in their tenant.
     */
    public function viewAny(User $authUser): Response|bool
    {
        return $authUser->hasPermission('users.view');
    }

    /**
     * Determine whether the user can view the model.
     * Tenant-aware: a user may only view users within their own tenant.
     */
    public function view(User $authUser, User $targetUser): Response|bool
    {
        if ($authUser->tenant_id !== $targetUser->tenant_id) {
            return $this->deny('You may not access users from another tenant.');
        }

        return $authUser->hasPermission('users.view') || $authUser->id === $targetUser->id;
    }

    /**
     * Determine whether the user can create new users within their tenant.
     */
    public function create(User $authUser): Response|bool
    {
        return $authUser->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update the model.
     * Tenant-aware: only users within the same tenant may be updated.
     */
    public function update(User $authUser, User $targetUser): Response|bool
    {
        if ($authUser->tenant_id !== $targetUser->tenant_id) {
            return $this->deny('You may not modify users from another tenant.');
        }

        if ($authUser->id === $targetUser->id) {
            return true;
        }

        return $authUser->hasPermission('users.update');
    }

    /**
     * Determine whether the user can delete the model.
     * Tenant-aware: a user cannot delete themselves, and tenant isolation is enforced.
     */
    public function delete(User $authUser, User $targetUser): Response|bool
    {
        if ($authUser->id === $targetUser->id) {
            return $this->deny('You cannot delete your own account.');
        }

        if ($authUser->tenant_id !== $targetUser->tenant_id) {
            return $this->deny('You may not delete users from another tenant.');
        }

        return $authUser->hasPermission('users.delete');
    }

    /**
     * Determine whether the user can restore a soft-deleted model.
     */
    public function restore(User $authUser, User $targetUser): Response|bool
    {
        if ($authUser->tenant_id !== $targetUser->tenant_id) {
            return $this->deny('You may not restore users from another tenant.');
        }

        return $authUser->hasPermission('users.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $authUser, User $targetUser): Response|bool
    {
        if ($authUser->tenant_id !== $targetUser->tenant_id) {
            return $this->deny('You may not force-delete users from another tenant.');
        }

        return $authUser->hasPermission('users.force_delete');
    }
}
