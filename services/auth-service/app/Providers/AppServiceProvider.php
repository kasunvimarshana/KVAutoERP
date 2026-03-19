<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AuthServiceContract;
use App\Contracts\IdentityProviderContract;
use App\Contracts\RevocationServiceContract;
use App\Contracts\SuspiciousActivityServiceContract;
use App\Contracts\TokenServiceContract;
use App\Contracts\UserServiceClientContract;
use App\Http\Clients\UserServiceClient;
use App\Providers\IdentityProviders\AzureAdIdentityProvider;
use App\Providers\IdentityProviders\KeycloakIdentityProvider;
use App\Providers\IdentityProviders\LocalIdentityProvider;
use App\Providers\IdentityProviders\OAuth2IdentityProvider;
use App\Providers\IdentityProviders\OktaIdentityProvider;
use App\Providers\IdentityProviders\SamlIdentityProvider;
use App\Services\AuthService;
use App\Services\IdentityProviderManager;
use App\Services\RevocationService;
use App\Services\SuspiciousActivityService;
use App\Services\TokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Core service bindings
        $this->app->singleton(TokenServiceContract::class, TokenService::class);
        $this->app->singleton(RevocationServiceContract::class, RevocationService::class);
        $this->app->singleton(UserServiceClientContract::class, UserServiceClient::class);
        $this->app->singleton(SuspiciousActivityServiceContract::class, SuspiciousActivityService::class);

        // IAM provider manager — register all built-in adapters
        $this->app->singleton(IdentityProviderManager::class, function ($app) {
            $manager = new IdentityProviderManager($app);
            $manager->register('local',    LocalIdentityProvider::class);
            $manager->register('okta',     OktaIdentityProvider::class);
            $manager->register('keycloak', KeycloakIdentityProvider::class);
            $manager->register('azure_ad', AzureAdIdentityProvider::class);
            $manager->register('oauth2',   OAuth2IdentityProvider::class);
            $manager->register('saml',     SamlIdentityProvider::class);

            return $manager;
        });

        // Auth service (depends on the above singletons)
        $this->app->singleton(AuthServiceContract::class, AuthService::class);
    }

    public function boot(): void
    {
        //
    }
}
