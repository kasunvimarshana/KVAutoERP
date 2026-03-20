<?php

declare(strict_types=1);

namespace LaravelDDD\Tests\Unit\Resolvers;

use Illuminate\Filesystem\Filesystem;
use LaravelDDD\Resolvers\ContextResolver;
use LaravelDDD\Tests\TestCase;

/**
 */
class ContextResolverTest extends TestCase
{
    private ContextResolver $resolver;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ContextResolver(new Filesystem(), 'App');
        $this->tempDir  = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ddd_test_'.uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->rmdirRecursive($this->tempDir);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_registers_a_context(): void
    {
        $this->resolver->register('ProductCatalog', '/some/path/ProductCatalog');

        $this->assertTrue($this->resolver->has('ProductCatalog'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_for_unregistered_context(): void
    {
        $this->assertFalse($this->resolver->has('NonExistent'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_retrieves_a_registered_context(): void
    {
        $this->resolver->register('OrderManagement', '/app/OrderManagement');

        $context = $this->resolver->get('OrderManagement');

        $this->assertNotNull($context);
        $this->assertSame('OrderManagement', $context['name']);
        $this->assertSame('/app/OrderManagement', $context['path']);
        $this->assertSame('App\\OrderManagement', $context['namespace']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_for_unknown_context(): void
    {
        $this->assertNull($this->resolver->get('Unknown'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_all_registered_contexts(): void
    {
        $this->resolver->register('ContextA', '/path/a');
        $this->resolver->register('ContextB', '/path/b');

        $all = $this->resolver->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('ContextA', $all);
        $this->assertArrayHasKey('ContextB', $all);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_discovers_contexts_with_domain_directory(): void
    {
        // Create a valid DDD context: has Domain/ sub-directory
        $contextPath = $this->tempDir.DIRECTORY_SEPARATOR.'ProductCatalog';
        mkdir($contextPath.DIRECTORY_SEPARATOR.'Domain', 0755, true);

        // Create a non-context directory (no Domain/)
        mkdir($this->tempDir.DIRECTORY_SEPARATOR.'NotAContext', 0755, true);

        $this->resolver->discover($this->tempDir);

        $this->assertTrue($this->resolver->has('ProductCatalog'));
        $this->assertFalse($this->resolver->has('NotAContext'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function discover_does_nothing_for_non_existent_path(): void
    {
        $this->resolver->discover('/non/existent/path');

        $this->assertEmpty($this->resolver->all());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function namespace_is_built_from_root(): void
    {
        $resolver = new ContextResolver(new Filesystem(), 'MyApp');
        $resolver->register('Billing', '/some/path');

        $context = $resolver->get('Billing');

        $this->assertSame('MyApp\\Billing', $context['namespace']);
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
