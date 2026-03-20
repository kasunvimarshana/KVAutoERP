<?php

declare(strict_types=1);

namespace LaravelDDD\Tests\Unit\Support;

use InvalidArgumentException;
use LaravelDDD\Support\StubRenderer;
use LaravelDDD\Tests\TestCase;

/**
 */
class StubRendererTest extends TestCase
{
    private StubRenderer $renderer;

    private string $defaultStubsPath;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultStubsPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'stubs';
        $this->tempDir          = sys_get_temp_dir().DIRECTORY_SEPARATOR.'stub_test_'.uniqid();
        mkdir($this->tempDir, 0755, true);

        $this->renderer = new StubRenderer($this->defaultStubsPath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (glob($this->tempDir.'/*') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($this->tempDir);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_replaces_a_simple_token(): void
    {
        $result = $this->renderer->render('Hello {{ name }}!', ['name' => 'World']);

        $this->assertSame('Hello World!', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_replaces_multiple_tokens(): void
    {
        $result = $this->renderer->render(
            'class {{ className }} in {{ namespace }}',
            ['className' => 'Product', 'namespace' => 'App\\Domain'],
        );

        $this->assertSame('class Product in App\\Domain', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_tokens_with_varying_spaces(): void
    {
        $stub = 'Hello {{name}} and {{ name }} and {{  name  }}!';

        $result = $this->renderer->render($stub, ['name' => 'World']);

        $this->assertSame('Hello World and World and World!', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_leaves_unknown_tokens_unreplaced(): void
    {
        $result = $this->renderer->render('Hello {{ unknown }}!', ['other' => 'value']);

        $this->assertSame('Hello {{ unknown }}!', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_from_a_file(): void
    {
        $stubPath = $this->tempDir.DIRECTORY_SEPARATOR.'test.stub';
        file_put_contents($stubPath, 'namespace {{ namespace }}; class {{ className }} {}');

        $result = $this->renderer->renderFile($stubPath, [
            'namespace' => 'App\\Entities',
            'className' => 'Product',
        ]);

        $this->assertSame('namespace App\\Entities; class Product {}', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function render_file_throws_for_missing_file(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->renderer->renderFile('/non/existent/stub.stub', []);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resolves_stub_path_from_default_directory(): void
    {
        $path = $this->renderer->getStubPath('entity');

        $this->assertStringEndsWith('entity.stub', $path);
        $this->assertFileExists($path);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prefers_custom_stub_over_default(): void
    {
        // Write a custom entity stub to temp dir
        $customContent = '<?php // custom entity';
        file_put_contents($this->tempDir.DIRECTORY_SEPARATOR.'entity.stub', $customContent);

        $renderer = new StubRenderer($this->defaultStubsPath, $this->tempDir);
        $path     = $renderer->getStubPath('entity');

        $this->assertSame($this->tempDir.DIRECTORY_SEPARATOR.'entity.stub', $path);
        $this->assertStringContainsString('custom entity', file_get_contents($path));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_when_stub_not_found(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->renderer->getStubPath('completely-nonexistent-stub');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_stub_path_appends_extension_if_missing(): void
    {
        $withExtension    = $this->renderer->getStubPath('entity.stub');
        $withoutExtension = $this->renderer->getStubPath('entity');

        $this->assertSame($withExtension, $withoutExtension);
    }
}
