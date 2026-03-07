<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind custom Role / Permission models so Spatie uses our extended versions
        $this->app->bind(
            \Spatie\Permission\Models\Role::class,
            \App\Domain\Auth\Entities\Role::class
        );

        $this->app->bind(
            \Spatie\Permission\Models\Permission::class,
            \App\Domain\Auth\Entities\Permission::class
        );
    }

    public function boot(): void
    {
        // Configure Passport token lifetimes
        Passport::tokensExpireIn(
            now()->addMinutes(config('passport.token_expire_in', 60))
        );
        Passport::refreshTokensExpireIn(
            now()->addMinutes(config('passport.refresh_token_expire_in', 20160))
        );
        Passport::personalAccessTokensExpireIn(
            now()->addMinutes(config('passport.personal_access_token_expire_in', 525600))
        );

        // Use UUID keys for OAuth clients
        Passport::useClientUUIDs();
    }
}
