<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Auth service provider — registers policies and gates.
 *
 * In this microservice the primary authorization mechanism is the JWT
 * middleware + RBAC/ABAC checks in controllers and services. Laravel
 * Policies can be registered here as the service matures.
 */
final class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policy mappings.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
