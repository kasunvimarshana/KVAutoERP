<?php

declare(strict_types=1);

namespace LaravelDDD;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use LaravelDDD\Commands\DddInfoCommand;
use LaravelDDD\Commands\ListContextsCommand;
use LaravelDDD\Commands\MakeAggregateRootCommand;
use LaravelDDD\Commands\MakeContextCommand;
use LaravelDDD\Commands\MakeCqrsCommandCommand;
use LaravelDDD\Commands\MakeCqrsQueryCommand;
use LaravelDDD\Commands\MakeDomainEventCommand;
use LaravelDDD\Commands\MakeDomainServiceCommand;
use LaravelDDD\Commands\MakeDtoCommand;
use LaravelDDD\Commands\MakeEntityCommand;
use LaravelDDD\Commands\MakeRepositoryCommand;
use LaravelDDD\Commands\MakeSpecificationCommand;
use LaravelDDD\Commands\MakeValueObjectCommand;
use LaravelDDD\Commands\PublishStubsCommand;
use LaravelDDD\Contracts\ContextRegistrar;
use LaravelDDD\Generators\AggregateRootGenerator;
use LaravelDDD\Generators\ContextGenerator;
use LaravelDDD\Generators\CqrsCommandGenerator;
use LaravelDDD\Generators\CqrsCommandHandlerGenerator;
use LaravelDDD\Generators\CqrsQueryGenerator;
use LaravelDDD\Generators\CqrsQueryHandlerGenerator;
use LaravelDDD\Generators\DomainEventGenerator;
use LaravelDDD\Generators\DomainServiceGenerator;
use LaravelDDD\Generators\DtoGenerator;
use LaravelDDD\Generators\EloquentRepositoryGenerator;
use LaravelDDD\Generators\EntityGenerator;
use LaravelDDD\Generators\RepositoryInterfaceGenerator;
use LaravelDDD\Generators\SpecificationGenerator;
use LaravelDDD\Generators\ValueObjectGenerator;
use LaravelDDD\Resolvers\ContextResolver;
use LaravelDDD\Support\FileGenerator;
use LaravelDDD\Support\StubRenderer;

/**
 * Service provider for the Laravel DDD Modules package.
 */
class DddServiceProvider extends ServiceProvider
{
    /**
     * Register package services and bindings.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ddd.php',
            'ddd',
        );

        // Bind the filesystem
        $this->app->singleton(Filesystem::class, fn () => new Filesystem());

        // Bind StubRenderer
        $this->app->singleton(StubRenderer::class, function ($app) {
            $defaultStubsPath = __DIR__.'/../stubs';
            $customStubsPath  = $app['config']->get('ddd.stubs_path');

            return new StubRenderer($defaultStubsPath, $customStubsPath);
        });

        // Bind FileGenerator
        $this->app->singleton(FileGenerator::class, fn ($app) => new FileGenerator($app->make(Filesystem::class)));

        // Bind ContextRegistrar / ContextResolver
        $this->app->singleton(ContextRegistrar::class, function ($app) {
            return new ContextResolver(
                $app->make(Filesystem::class),
                (string) $app['config']->get('ddd.namespace_root', 'App'),
            );
        });

        $this->app->alias(ContextRegistrar::class, 'ddd');

        // Bind all generators
        $this->bindGenerators();
    }

    /**
     * Bootstrap package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishStubs();
            $this->registerCommands();
        }

        if ((bool) $this->app['config']->get('ddd.auto_discover_contexts', true)) {
            $this->autoDiscoverContexts();
        }
    }

    /**
     * Return the services provided by this provider.
     *
     * @return list<string>
     */
    public function provides(): array
    {
        return [
            ContextRegistrar::class,
            'ddd',
            StubRenderer::class,
            FileGenerator::class,
        ];
    }

    /**
     * Publish the configuration file.
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        $this->publishes(
            [__DIR__.'/../config/ddd.php' => config_path('ddd.php')],
            'ddd-config',
        );
    }

    /**
     * Publish the stub files.
     *
     * @return void
     */
    protected function publishStubs(): void
    {
        $this->publishes(
            [__DIR__.'/../stubs' => base_path('stubs/ddd')],
            'ddd-stubs',
        );
    }

    /**
     * Register all Artisan commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            MakeContextCommand::class,
            MakeEntityCommand::class,
            MakeValueObjectCommand::class,
            MakeAggregateRootCommand::class,
            MakeDomainEventCommand::class,
            MakeDomainServiceCommand::class,
            MakeSpecificationCommand::class,
            MakeRepositoryCommand::class,
            MakeCqrsCommandCommand::class,
            MakeCqrsQueryCommand::class,
            MakeDtoCommand::class,
            ListContextsCommand::class,
            PublishStubsCommand::class,
            DddInfoCommand::class,
        ]);
    }

    /**
     * Auto-discover bounded contexts under the configured base path.
     *
     * @return void
     */
    protected function autoDiscoverContexts(): void
    {
        /** @var ContextRegistrar $registrar */
        $registrar = $this->app->make(ContextRegistrar::class);
        $basePath  = base_path((string) $this->app['config']->get('ddd.base_path', 'app'));

        $registrar->discover($basePath);

        if (! (bool) $this->app['config']->get('ddd.auto_register_providers', true)) {
            return;
        }

        foreach ($registrar->all() as $context) {
            $providerClass = $context['namespace'].'\\'.$context['name'].'ServiceProvider';

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Bind all generator classes into the container.
     *
     * @return void
     */
    protected function bindGenerators(): void
    {
        $generators = [
            ContextGenerator::class,
            EntityGenerator::class,
            ValueObjectGenerator::class,
            AggregateRootGenerator::class,
            DomainEventGenerator::class,
            DomainServiceGenerator::class,
            SpecificationGenerator::class,
            RepositoryInterfaceGenerator::class,
            EloquentRepositoryGenerator::class,
            CqrsCommandGenerator::class,
            CqrsCommandHandlerGenerator::class,
            CqrsQueryGenerator::class,
            CqrsQueryHandlerGenerator::class,
            DtoGenerator::class,
        ];

        foreach ($generators as $generatorClass) {
            $this->app->singleton($generatorClass, fn ($app) => new $generatorClass(
                $app['config'],
                $app->make(StubRenderer::class),
                $app->make(FileGenerator::class),
            ));
        }
    }
}
