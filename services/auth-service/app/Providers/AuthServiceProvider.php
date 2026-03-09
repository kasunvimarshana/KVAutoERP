<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\User\Entities\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        // ---- Passport configuration ----------------------------------------

        // Use custom token lifetimes from config
        Passport::tokensExpireIn(
            now()->addMinutes((int) config('passport.token_lifetime', 1440))
        );

        Passport::refreshTokensExpireIn(
            now()->addDays((int) config('passport.refresh_token_lifetime', 30))
        );

        Passport::personalAccessTokensExpireIn(
            now()->addMonths((int) config('passport.personal_access_token_lifetime', 6))
        );

        // Define all available OAuth scopes
        Passport::tokensCan([
            '*'            => 'Full access',
            'read'         => 'Read resources',
            'write'        => 'Create and update resources',
            'delete'       => 'Delete resources',
            'manage-users' => 'Manage users within the tenant',
        ]);

        Passport::setDefaultScope('read');

        // ---- Gates ----------------------------------------------------------

        // Super-admin bypasses all policy checks
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('super-admin')) {
                return true;
            }

            return null; // defer to policies
        });

        // Role-based gates
        Gate::define('manage-users', fn (User $user): bool => $user->hasPermissionTo('manage-users'));
        Gate::define('admin-access', fn (User $user): bool => $user->hasAnyRole(['admin', 'super-admin']));
    }
}
