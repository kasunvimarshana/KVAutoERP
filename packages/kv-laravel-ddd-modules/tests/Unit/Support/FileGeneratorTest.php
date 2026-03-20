<?php

declare(strict_types=1);

namespace LaravelDDD\Tests\Unit\Support;

use Illuminate\Filesystem\Filesystem;
use LaravelDDD\Support\FileGenerator;
use LaravelDDD\Tests\TestCase;

/**
 */
class FileGeneratorTest extends TestCase
{
    private FileGenerator $generator;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir   = sys_get_temp_dir().DIRECTORY_SEPARATOR.'filegenerator_test_'.uniqid();
        $this->generator = new FileGenerator(new Filesystem());
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->rmdirRecursive($this->tempDir);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_file_with_given_content(): void
    {
        $path = $this->tempDir.DIRECTORY_SEPARATOR.'Test.php';

        $result = $this->generator->generate($path, '<?php echo "hello";');

        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertStringContainsString('hello', file_get_contents($path));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_when_file_exists_and_force_is_false(): void
    {
        $path = $this->tempDir.DIRECTORY_SEPARATOR.'Existing.php';
        file_put_contents($path, '<?php // original');

        $result = $this->generator->generate($path, '<?php // new content');

        $this->assertFalse($result);
        $this->assertStringContainsString('original', file_get_contents($path));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_overwrites_when_force_is_true(): void
    {
        $path = $this->tempDir.DIRECTORY_SEPARATOR.'Overwrite.php';
        file_put_contents($path, '<?php // original');

        $result = $this->generator->generate($path, '<?php // replaced', true);

        $this->assertTrue($result);
        $this->assertStringContainsString('replaced', file_get_contents($path));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_nested_directories_automatically(): void
    {
        $path = $this->tempDir.DIRECTORY_SEPARATOR.'deep'.DIRECTORY_SEPARATOR.'nested'.DIRECTORY_SEPARATOR.'Class.php';

        $this->generator->generate($path, '<?php class A {}');

        $this->assertFileExists($path);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ensure_directory_exists_creates_nested_dirs(): void
    {
        $dirPath = $this->tempDir.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'b'.DIRECTORY_SEPARATOR.'c';

        $this->generator->ensureDirectoryExists($dirPath);

        $this->assertDirectoryExists($dirPath);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ensure_directory_exists_is_idempotent(): void
    {
        $dirPath = $this->tempDir.DIRECTORY_SEPARATOR.'idempotent';
        mkdir($dirPath);

        // Should not throw
        $this->generator->ensureDirectoryExists($dirPath);

        $this->assertDirectoryExists($dirPath);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function file_exists_returns_correct_value(): void
    {
        $existing    = $this->tempDir.DIRECTORY_SEPARATOR.'exists.php';
        $nonExistent = $this->tempDir.DIRECTORY_SEPARATOR.'nope.php';

        file_put_contents($existing, '<?php');

        $this->assertTrue($this->generator->fileExists($existing));
        $this->assertFalse($this->generator->fileExists($nonExistent));
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
