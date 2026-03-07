<?php

namespace App\Providers;

use App\Services\KeycloakService;
use App\Services\MessageBrokerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KeycloakService::class, fn () => new KeycloakService());
        $this->app->singleton(MessageBrokerService::class, fn () => new MessageBrokerService());
    }

    public function boot(): void
    {
        //
    }
}
