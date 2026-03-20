<?php

declare(strict_types=1);

namespace LaravelDDD\Tests\Feature\Commands;

use Illuminate\Filesystem\Filesystem;
use LaravelDDD\Tests\TestCase;

/**
 */
class MakeEntityCommandTest extends TestCase
{
    private string $tempDir;

    private Filesystem $files;

    protected function setUp(): void
    {
        // Create tempDir BEFORE parent::setUp() so getEnvironmentSetUp() can use it
        if (! isset($this->tempDir)) {
            $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'make_entity_test_'.uniqid();
            mkdir($this->tempDir, 0755, true);
        }

        $this->files = new Filesystem();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->rmdirRecursive($this->tempDir);
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('ddd.base_path', $this->tempDir);
        $app['config']->set('ddd.namespace_root', 'App');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_entity_file_in_correct_location(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'ProductCatalog',
            'name'    => 'Product',
        ])->assertSuccessful();

        $expectedPath = $this->tempDir
            .DIRECTORY_SEPARATOR.'ProductCatalog'
            .DIRECTORY_SEPARATOR.'Domain'
            .DIRECTORY_SEPARATOR.'Entities'
            .DIRECTORY_SEPARATOR.'Product.php';

        $this->assertFileExists($expectedPath);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function entity_file_contains_correct_class_name(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'OrderManagement',
            'name'    => 'Order',
        ])->assertSuccessful();

        $path    = $this->tempDir
            .DIRECTORY_SEPARATOR.'OrderManagement'
            .DIRECTORY_SEPARATOR.'Domain'
            .DIRECTORY_SEPARATOR.'Entities'
            .DIRECTORY_SEPARATOR.'Order.php';
        $content = file_get_contents($path);

        $this->assertStringContainsString('class Order', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function entity_file_contains_correct_namespace(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'Billing',
            'name'    => 'Invoice',
        ])->assertSuccessful();

        $path    = $this->tempDir
            .DIRECTORY_SEPARATOR.'Billing'
            .DIRECTORY_SEPARATOR.'Domain'
            .DIRECTORY_SEPARATOR.'Entities'
            .DIRECTORY_SEPARATOR.'Invoice.php';
        $content = file_get_contents($path);

        $this->assertStringContainsString('namespace App\\Billing\\Domain\\Entities', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_failure_when_entity_exists_without_force(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'Catalog',
            'name'    => 'Item',
        ])->assertSuccessful();

        $this->artisan('ddd:make-entity', [
            'context' => 'Catalog',
            'name'    => 'Item',
        ])->assertFailed();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_overwrites_entity_with_force_option(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'Catalog',
            'name'    => 'Widget',
        ])->assertSuccessful();

        $path = $this->tempDir
            .DIRECTORY_SEPARATOR.'Catalog'
            .DIRECTORY_SEPARATOR.'Domain'
            .DIRECTORY_SEPARATOR.'Entities'
            .DIRECTORY_SEPARATOR.'Widget.php';
        file_put_contents($path, '<?php // original');

        $this->artisan('ddd:make-entity', [
            'context' => 'Catalog',
            'name'    => 'Widget',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('class Widget', file_get_contents($path));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function entity_implements_entity_contract(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'Shipping',
            'name'    => 'Shipment',
        ])->assertSuccessful();

        $path    = $this->tempDir
            .DIRECTORY_SEPARATOR.'Shipping'
            .DIRECTORY_SEPARATOR.'Domain'
            .DIRECTORY_SEPARATOR.'Entities'
            .DIRECTORY_SEPARATOR.'Shipment.php';
        $content = file_get_contents($path);

        $this->assertStringContainsString('EntityContract', $content);
    }

    // Helper

    private function rmdirRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}
