<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Domain\Tenant\Services\TenantProvisioningService;
use App\Infrastructure\Repositories\EloquentTenantRepository;
use App\Infrastructure\Runtime\RuntimeConfigManager;
use App\Application\Tenant\Handlers\CreateTenantCommandHandler;
use App\Application\Tenant\Handlers\DeleteTenantCommandHandler;
use App\Application\Tenant\Handlers\UpdateTenantCommandHandler;
use App\Application\Tenant\Handlers\UpdateTenantConfigCommandHandler;
use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider.
 *
 * Binds all domain interfaces to concrete implementations and wires up
 * the command/query handler chain for the Tenant Service.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register bindings into the service container.
     */
    public function register(): void
    {
        // Repository
        $this->app->bind(TenantRepositoryInterface::class, EloquentTenantRepository::class);

        // Domain services
        $this->app->singleton(TenantProvisioningService::class, function ($app) {
            return new TenantProvisioningService(
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });

        $this->app->singleton(RuntimeConfigManager::class, function ($app) {
            return new RuntimeConfigManager(
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });

        // Command handlers
        $this->app->bind(CreateTenantCommandHandler::class, function ($app) {
            return new CreateTenantCommandHandler(
                tenantRepository: $app->make(TenantRepositoryInterface::class),
                provisioningService: $app->make(TenantProvisioningService::class),
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });

        $this->app->bind(UpdateTenantCommandHandler::class, function ($app) {
            return new UpdateTenantCommandHandler(
                tenantRepository: $app->make(TenantRepositoryInterface::class),
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });

        $this->app->bind(DeleteTenantCommandHandler::class, function ($app) {
            return new DeleteTenantCommandHandler(
                tenantRepository: $app->make(TenantRepositoryInterface::class),
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });

        $this->app->bind(UpdateTenantConfigCommandHandler::class, function ($app) {
            return new UpdateTenantConfigCommandHandler(
                tenantRepository: $app->make(TenantRepositoryInterface::class),
                runtimeConfig: $app->make(RuntimeConfigManager::class),
                logger: $app->make(\Psr\Log\LoggerInterface::class),
            );
        });

        // Application service
        $this->app->bind(TenantService::class, function ($app) {
            return new TenantService(
                repository: $app->make(TenantRepositoryInterface::class),
                createHandler: $app->make(CreateTenantCommandHandler::class),
                updateHandler: $app->make(UpdateTenantCommandHandler::class),
                deleteHandler: $app->make(DeleteTenantCommandHandler::class),
                configHandler: $app->make(UpdateTenantConfigCommandHandler::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
