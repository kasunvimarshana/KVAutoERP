<?php

namespace LaravelDddModules\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use LaravelDddModules\Generators\AbstractGenerator;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;
use LaravelDddModules\Tests\TestCase;

class AbstractGeneratorTest extends TestCase
{
    public function test_module_generator_extends_abstract_generator(): void
    {
        $files    = new Filesystem();
        $compiler = new StubCompiler($files);
        $generator = new ModuleGenerator($files, $compiler);

        $this->assertInstanceOf(AbstractGenerator::class, $generator);
    }

    public function test_abstract_generator_can_be_extended(): void
    {
        $files    = new Filesystem();
        $compiler = new StubCompiler($files);

        // Create an anonymous concrete implementation
        $generator = new class($files, $compiler) extends AbstractGenerator {
            public function generate(string $name, array $options = []): array
            {
                return ['directories' => [], 'files' => []];
            }

            public function buildReplacements(string $name, string $namespace): array
            {
                return ['{{ name }}' => $name];
            }

            public function getResolvedStubPath(): string
            {
                return $this->resolveStubPath();
            }
        };

        $this->assertInstanceOf(AbstractGenerator::class, $generator);
        $result = $generator->generate('Test');
        $this->assertArrayHasKey('directories', $result);
        $this->assertArrayHasKey('files', $result);
    }

    public function test_resolve_stub_path_returns_package_stubs_by_default(): void
    {
        $files    = new Filesystem();
        $compiler = new StubCompiler($files);

        $generator = new class($files, $compiler) extends AbstractGenerator {
            public function generate(string $name, array $options = []): array { return ['directories' => [], 'files' => []]; }
            public function buildReplacements(string $name, string $namespace): array { return []; }
            public function exposedResolveStubPath(): string { return $this->resolveStubPath(); }
        };

        $stubPath = $generator->exposedResolveStubPath();
        $this->assertDirectoryExists($stubPath);
        $this->assertFileExists("{$stubPath}/Entity.stub");
    }
}
