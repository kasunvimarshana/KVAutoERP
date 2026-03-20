<?php

namespace LaravelDddModules\Tests;

use LaravelDddModules\Providers\DddModulesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DddModulesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ddd-modules.modules_path', sys_get_temp_dir() . '/ddd-test-modules');
        $app['config']->set('ddd-modules.modules_namespace', 'App\\Modules');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupTestModules();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestModules();
        parent::tearDown();
    }

    protected function cleanupTestModules(): void
    {
        $path = sys_get_temp_dir() . '/ddd-test-modules';
        if (is_dir($path)) {
            $this->deleteDirectory($path);
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $fullPath = "$dir/$item";
            is_dir($fullPath) ? $this->deleteDirectory($fullPath) : unlink($fullPath);
        }
        rmdir($dir);
    }
}
