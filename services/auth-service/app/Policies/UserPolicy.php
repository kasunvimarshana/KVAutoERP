<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasRole('super-admin');
    }
}
