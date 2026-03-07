<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return in_array($authUser->role, ['admin', 'manager'], true);
    }

    public function view(User $authUser, User $user): bool
    {
        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }
        return $authUser->role === 'admin'
            || $authUser->role === 'manager'
            || $authUser->id === $user->id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->role === 'admin';
    }

    public function update(User $authUser, User $user): bool
    {
        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }
        return $authUser->role === 'admin'
            || $authUser->role === 'manager'
            || $authUser->id === $user->id;
    }

    public function delete(User $authUser, User $user): bool
    {
        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }
        return $authUser->role === 'admin';
    }

    public function assignRole(User $authUser, User $user): bool
    {
        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }
        return $authUser->role === 'admin';
    }

    public function updatePermissions(User $authUser, User $user): bool
    {
        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }
        return $authUser->role === 'admin';
    }
}
