<?php

declare(strict_types=1);

namespace LaravelDDD\Tests;

use LaravelDDD\DddServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for the Laravel DDD Modules package.
 */
abstract class TestCase extends Orchestra
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [
            DddServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ddd.namespace_root', 'App');
        $app['config']->set('ddd.base_path', 'app');
        $app['config']->set('ddd.auto_discover_contexts', false);
        $app['config']->set('ddd.auto_register_providers', false);
    }
}
