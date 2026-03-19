<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\FeatureFlagRepositoryInterface;
use App\Contracts\Repositories\FormDefinitionRepositoryInterface;
use App\Contracts\Repositories\ModuleRegistryRepositoryInterface;
use App\Contracts\Repositories\TenantConfigurationRepositoryInterface;
use App\Contracts\Repositories\WorkflowDefinitionRepositoryInterface;
use App\Contracts\Services\FeatureFlagServiceInterface;
use App\Contracts\Services\FormDefinitionServiceInterface;
use App\Contracts\Services\ModuleRegistryServiceInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use App\Contracts\Services\WorkflowDefinitionServiceInterface;
use App\Repositories\FeatureFlagRepository;
use App\Repositories\FormDefinitionRepository;
use App\Repositories\ModuleRegistryRepository;
use App\Repositories\TenantConfigurationRepository;
use App\Repositories\WorkflowDefinitionRepository;
use App\Services\FeatureFlagService;
use App\Services\FormDefinitionService;
use App\Services\ModuleRegistryService;
use App\Services\TenantConfigService;
use App\Services\WorkflowDefinitionService;
use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * All interface → implementation bindings.
     * Swap implementations here without touching business logic.
     */
    private array $repositoryBindings = [
        TenantConfigurationRepositoryInterface::class => TenantConfigurationRepository::class,
        FeatureFlagRepositoryInterface::class         => FeatureFlagRepository::class,
        FormDefinitionRepositoryInterface::class      => FormDefinitionRepository::class,
        WorkflowDefinitionRepositoryInterface::class  => WorkflowDefinitionRepository::class,
        ModuleRegistryRepositoryInterface::class      => ModuleRegistryRepository::class,
    ];

    private array $serviceBindings = [
        TenantConfigServiceInterface::class      => TenantConfigService::class,
        FeatureFlagServiceInterface::class       => FeatureFlagService::class,
        FormDefinitionServiceInterface::class    => FormDefinitionService::class,
        WorkflowDefinitionServiceInterface::class => WorkflowDefinitionService::class,
        ModuleRegistryServiceInterface::class    => ModuleRegistryService::class,
    ];

    public function register(): void
    {
        foreach ($this->repositoryBindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        foreach ($this->serviceBindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    public function boot(): void {}
}
