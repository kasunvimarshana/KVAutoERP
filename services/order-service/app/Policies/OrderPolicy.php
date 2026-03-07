<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id;
    }

    public function create(User $user): bool { return true; }

    public function update(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id && in_array($user->role, ['admin', 'manager'], true);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id && $user->role === 'admin';
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id && in_array($user->role, ['admin', 'manager'], true);
    }
}
