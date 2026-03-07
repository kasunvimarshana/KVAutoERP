<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'user'], true);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id
            && in_array($user->role, ['admin', 'manager'], true);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id
            && $user->role === 'admin';
    }
}
