<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Organization\Services\OrganizationService;
use App\Application\Tenant\Services\TenantService;
use App\Application\Tenant\Services\TenantServiceInterface;
use App\Application\Webhook\Services\WebhookService;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;
use App\Infrastructure\Repositories\OrganizationRepository;
use App\Infrastructure\Repositories\TenantRepository;
use App\Infrastructure\RuntimeConfig\RuntimeConfigManager;
use App\Infrastructure\Webhook\WebhookDispatcher;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        TenantRepositoryInterface::class      => TenantRepository::class,
        OrganizationRepositoryInterface::class => OrganizationRepository::class,
    ];

    public function register(): void
    {
        // RuntimeConfigManager (singleton: one instance per request)
        $this->app->singleton(RuntimeConfigManager::class, function ($app): RuntimeConfigManager {
            return new RuntimeConfigManager(
                $app->make(TenantRepositoryInterface::class)
            );
        });

        // TenantService
        $this->app->bind(TenantServiceInterface::class, function ($app): TenantService {
            return new TenantService(
                $app->make(TenantRepositoryInterface::class),
                $app->make(RuntimeConfigManager::class),
            );
        });

        // OrganizationService
        $this->app->bind(OrganizationService::class, function ($app): OrganizationService {
            return new OrganizationService(
                $app->make(OrganizationRepositoryInterface::class)
            );
        });

        // WebhookDispatcher
        $this->app->singleton(WebhookDispatcher::class, fn () => new WebhookDispatcher());

        // WebhookService
        $this->app->bind(WebhookService::class, function ($app): WebhookService {
            return new WebhookService($app->make(WebhookDispatcher::class));
        });

        // EventPublisher
        $this->app->singleton(EventPublisher::class, fn () => new EventPublisher());
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
