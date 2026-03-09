<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * Auth Service Provider.
 *
 * Registers Laravel Passport, configures token lifetimes, and defines
 * Gate abilities for role-based access control.
 */
final class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorisation services.
     */
    public function boot(): void
    {
        $this->configurePassport();
        $this->registerGates();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Passport configuration
    // ──────────────────────────────────────────────────────────────────────

    private function configurePassport(): void
    {
        // Token expiry windows.
        Passport::tokensExpireIn(
            now()->addMinutes((int) config('passport.token_expiry', 60))
        );

        Passport::refreshTokensExpireIn(
            now()->addDays((int) config('passport.refresh_token_expiry', 30))
        );

        Passport::personalAccessTokensExpireIn(
            now()->addMonths((int) config('passport.personal_access_token_expiry', 6))
        );

        // Use UUIDs for all token IDs.
        Passport::useUuids();

        // Hash token secrets in the database.
        Passport::hashClientSecrets();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Gate definitions
    // ──────────────────────────────────────────────────────────────────────

    private function registerGates(): void
    {
        // Role-based gates — check via Spatie HasRoles.
        Gate::define('is-super-admin', fn ($user) => $user->hasRole('super-admin'));
        Gate::define('is-admin', fn ($user) => $user->hasRole('admin') || $user->hasRole('super-admin'));
        Gate::define('is-manager', fn ($user) => $user->hasAnyRole(['manager', 'admin', 'super-admin']));
        Gate::define('is-staff', fn ($user) => $user->hasAnyRole(['staff', 'manager', 'admin', 'super-admin']));

        // Permission-based gates.
        Gate::define('manage-users', fn ($user) => $user->hasPermissionTo('manage-users'));
        Gate::define('manage-tenant', fn ($user) => $user->hasPermissionTo('manage-tenant'));
        Gate::define('view-reports', fn ($user) => $user->hasPermissionTo('view-reports'));
        Gate::define('manage-inventory', fn ($user) => $user->hasPermissionTo('manage-inventory'));
        Gate::define('manage-orders', fn ($user) => $user->hasPermissionTo('manage-orders'));

        // Tenant-scoped ownership check.
        Gate::define('access-tenant', function ($user, string $tenantId): bool {
            return $user->tenant_id === $tenantId || $user->hasRole('super-admin');
        });
    }
}
