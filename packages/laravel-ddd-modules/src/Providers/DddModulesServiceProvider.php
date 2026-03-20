<?php

namespace LaravelDddModules\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use LaravelDddModules\Commands\DddInfoCommand;
use LaravelDddModules\Commands\DddPublishCommand;
use LaravelDddModules\Commands\ListDDDModulesCommand;
use LaravelDddModules\Commands\MakeDDDAggregateCommand;
use LaravelDddModules\Commands\MakeDDDCqrsCommandCommand;
use LaravelDddModules\Commands\MakeDDDCqrsQueryCommand;
use LaravelDddModules\Commands\MakeDDDDomainEventCommand;
use LaravelDddModules\Commands\MakeDDDDomainServiceCommand;
use LaravelDddModules\Commands\MakeDDDDtoCommand;
use LaravelDddModules\Commands\MakeDDDEntityCommand;
use LaravelDddModules\Commands\MakeDDDHandlerCommand;
use LaravelDddModules\Commands\MakeDDDMigrationCommand;
use LaravelDddModules\Commands\MakeDDDModuleCommand;
use LaravelDddModules\Commands\MakeDDDRepositoryCommand;
use LaravelDddModules\Commands\MakeDDDSharedCommand;
use LaravelDddModules\Commands\MakeDDDSpecificationCommand;
use LaravelDddModules\Commands\MakeDDDUseCaseCommand;
use LaravelDddModules\Commands\MakeDDDValueObjectCommand;

class DddModulesServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ddd-modules.php',
            'ddd-modules'
        );
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishStubs();
            $this->registerCommands();
        }

        if (config('ddd-modules.auto_discover', true)) {
            $this->autoDiscoverModules();
        }
    }

    /**
     * Publish the config file.
     */
    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ddd-modules.php' => config_path('ddd-modules.php'),
        ], 'ddd-modules-config');
    }

    /**
     * Publish stub files for customization.
     */
    protected function publishStubs(): void
    {
        $this->publishes([
            __DIR__ . '/../../stubs' => base_path('stubs/ddd-modules'),
        ], 'ddd-modules-stubs');
    }

    /**
     * Register all package Artisan commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            // Module scaffolding
            MakeDDDModuleCommand::class,
            MakeDDDSharedCommand::class,

            // Domain layer generators
            MakeDDDEntityCommand::class,
            MakeDDDValueObjectCommand::class,
            MakeDDDAggregateCommand::class,
            MakeDDDDomainEventCommand::class,
            MakeDDDDomainServiceCommand::class,
            MakeDDDSpecificationCommand::class,
            MakeDDDRepositoryCommand::class,

            // Application layer generators
            MakeDDDDtoCommand::class,
            MakeDDDUseCaseCommand::class,
            MakeDDDCqrsCommandCommand::class,
            MakeDDDCqrsQueryCommand::class,
            MakeDDDHandlerCommand::class,

            // Infrastructure generators
            MakeDDDMigrationCommand::class,

            // Utility commands
            ListDDDModulesCommand::class,
            DddInfoCommand::class,
            DddPublishCommand::class,
        ]);
    }

    /**
     * Auto-discover and register module service providers, routes, views, and migrations.
     */
    protected function autoDiscoverModules(): void
    {
        $modulesPath      = config('ddd-modules.modules_path', app_path('Modules'));
        $modulesNamespace = config('ddd-modules.modules_namespace', 'App\\Modules');

        if (! File::isDirectory($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $moduleName    = basename($modulePath);
            $providerClass = "{$modulesNamespace}\\{$moduleName}\\Infrastructure\\Providers\\{$moduleName}ServiceProvider";

            if (class_exists($providerClass)) {
                // The ServiceProvider is responsible for loading its own routes/views/migrations.
                $this->app->register($providerClass);
                continue;
            }

            // No ServiceProvider found — load module resources directly so the module
            // remains functional even without a registered ServiceProvider.
            $this->loadModuleRoutes($modulePath);
            $this->loadModuleViews($modulePath, $moduleName);
            $this->loadModuleMigrations($modulePath);
        }
    }

    /**
     * Load routes from a module's Presentation/Http/Routes directory.
     */
    protected function loadModuleRoutes(string $modulePath): void
    {
        $apiRoutes = "{$modulePath}/Presentation/Http/Routes/api.php";
        $webRoutes = "{$modulePath}/Presentation/Http/Routes/web.php";

        if (File::exists($apiRoutes)) {
            Route::middleware('api')
                ->prefix('api')
                ->group($apiRoutes);
        }

        if (File::exists($webRoutes)) {
            Route::middleware('web')
                ->group($webRoutes);
        }
    }

    /**
     * Load Blade views from a module's Presentation/Views directory.
     */
    protected function loadModuleViews(string $modulePath, string $moduleName): void
    {
        $viewsPath = "{$modulePath}/Presentation/Views";

        if (File::isDirectory($viewsPath)) {
            $this->loadViewsFrom($viewsPath, Str::kebab($moduleName));
        }
    }

    /**
     * Load migrations from a module's Infrastructure/Persistence/Migrations directory.
     */
    protected function loadModuleMigrations(string $modulePath): void
    {
        $migrationsPath = "{$modulePath}/Infrastructure/Persistence/Migrations";

        if (File::isDirectory($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}

