<?php

declare(strict_types=1);

namespace LaravelDDD\Tests\Feature\Commands;

use Illuminate\Filesystem\Filesystem;
use LaravelDDD\Tests\TestCase;

/**
 */
class MakeContextCommandTest extends TestCase
{
    private string $tempDir;

    private Filesystem $files;

    protected function setUp(): void
    {
        // Create tempDir BEFORE parent::setUp() so getEnvironmentSetUp() can use it
        if (! isset($this->tempDir)) {
            $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'make_context_test_'.uniqid();
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
    public function it_creates_the_context_service_provider(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'ProductCatalog'])
            ->assertSuccessful();

        $providerPath = $this->tempDir.DIRECTORY_SEPARATOR.'ProductCatalog'.DIRECTORY_SEPARATOR.'ProductCatalogServiceProvider.php';

        $this->assertFileExists($providerPath);
        $this->assertStringContainsString('class ProductCatalogServiceProvider', file_get_contents($providerPath));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_domain_layer_directories(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'OrderManagement'])
            ->assertSuccessful();

        $contextRoot = $this->tempDir.DIRECTORY_SEPARATOR.'OrderManagement';

        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Domain');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR.'Entities');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR.'ValueObjects');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR.'Repositories');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR.'Events');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_application_layer_directories(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'Billing'])
            ->assertSuccessful();

        $contextRoot = $this->tempDir.DIRECTORY_SEPARATOR.'Billing';

        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Application');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'Commands');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'Queries');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'Handlers');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_infrastructure_layer_directories(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'Shipping'])
            ->assertSuccessful();

        $contextRoot = $this->tempDir.DIRECTORY_SEPARATOR.'Shipping';

        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Infrastructure');
        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR.'Persistence');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_presentation_layer_directories(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'Inventory'])
            ->assertSuccessful();

        $contextRoot = $this->tempDir.DIRECTORY_SEPARATOR.'Inventory';

        $this->assertDirectoryExists($contextRoot.DIRECTORY_SEPARATOR.'Presentation');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function force_option_overwrites_existing_provider(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'Catalog'])->assertSuccessful();
        $providerPath = $this->tempDir.DIRECTORY_SEPARATOR.'Catalog'.DIRECTORY_SEPARATOR.'CatalogServiceProvider.php';
        file_put_contents($providerPath, '<?php // original');

        $this->artisan('ddd:make-context', ['name' => 'Catalog', '--force' => true])
            ->assertSuccessful();

        $this->assertStringContainsString('class CatalogServiceProvider', file_get_contents($providerPath));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function provider_file_contains_correct_namespace(): void
    {
        $this->artisan('ddd:make-context', ['name' => 'UserManagement'])
            ->assertSuccessful();

        $providerPath = $this->tempDir.DIRECTORY_SEPARATOR.'UserManagement'.DIRECTORY_SEPARATOR.'UserManagementServiceProvider.php';
        $content      = file_get_contents($providerPath);

        $this->assertStringContainsString('namespace App\\UserManagement', $content);
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
