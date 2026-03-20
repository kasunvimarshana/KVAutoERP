<?php

namespace LaravelDddModules\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use LaravelDddModules\Generators\StubCompiler;
use LaravelDddModules\Tests\TestCase;

class StubCompilerTest extends TestCase
{
    private StubCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = new StubCompiler(new Filesystem());
    }

    public function test_it_replaces_placeholders_in_content(): void
    {
        $content = 'namespace {{ namespace }}\Domain; class {{ module }}Entity {}';
        $replacements = [
            '{{ namespace }}' => 'App\\Modules\\Order',
            '{{ module }}'    => 'Order',
        ];

        $result = $this->compiler->replace($content, $replacements);

        $this->assertStringContainsString('App\\Modules\\Order', $result);
        $this->assertStringContainsString('OrderEntity', $result);
        $this->assertStringNotContainsString('{{ namespace }}', $result);
        $this->assertStringNotContainsString('{{ module }}', $result);
    }

    public function test_it_compiles_a_stub_file(): void
    {
        $stubPath = sys_get_temp_dir() . '/test.stub';
        file_put_contents($stubPath, 'class {{ module }}Entity {}');

        $result = $this->compiler->compile($stubPath, ['{{ module }}' => 'Order']);

        $this->assertSame('class OrderEntity {}', $result);
        unlink($stubPath);
    }

    public function test_it_handles_multiple_occurrences(): void
    {
        $content = '{{ module }} and {{ module }} again';
        $result = $this->compiler->replace($content, ['{{ module }}' => 'Test']);
        $this->assertSame('Test and Test again', $result);
    }
}
