<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ABAC Gate: check if user belongs to tenant
        Gate::define('manage-tenant', function (User $user, string $tenantId) {
            return $user->tenant_id === $tenantId || $user->hasRole('super-admin');
        });
    }
}
