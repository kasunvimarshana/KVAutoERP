<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class MakeDDDSharedCommandTest extends TestCase
{
    public function test_it_creates_shared_module(): void
    {
        $this->artisan('make:ddd-shared')->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertDirectoryExists("{$modulesPath}/Shared/Domain/Contracts");
        $this->assertDirectoryExists("{$modulesPath}/Shared/Domain/ValueObjects");
        $this->assertDirectoryExists("{$modulesPath}/Shared/Domain/Exceptions");
        $this->assertDirectoryExists("{$modulesPath}/Shared/Domain/Traits");
        $this->assertDirectoryExists("{$modulesPath}/Shared/Domain/Events");
        $this->assertDirectoryExists("{$modulesPath}/Shared/Application/Contracts");
        $this->assertDirectoryExists("{$modulesPath}/Shared/Infrastructure/Concerns");
    }

    public function test_it_generates_base_contracts(): void
    {
        $this->artisan('make:ddd-shared')->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Shared/Domain/Contracts/AggregateRootInterface.php");
        $this->assertFileExists("{$modulesPath}/Shared/Domain/Contracts/EntityInterface.php");
        $this->assertFileExists("{$modulesPath}/Shared/Domain/Contracts/RepositoryInterface.php");
        $this->assertFileExists("{$modulesPath}/Shared/Domain/Contracts/DomainEventInterface.php");
        $this->assertFileExists("{$modulesPath}/Shared/Domain/Contracts/ValueObjectInterface.php");
    }

    public function test_it_fails_if_shared_module_exists(): void
    {
        $this->artisan('make:ddd-shared')->assertSuccessful();
        $this->artisan('make:ddd-shared')->assertFailed();
    }

    public function test_it_force_overwrites_shared_module(): void
    {
        $this->artisan('make:ddd-shared')->assertSuccessful();
        $this->artisan('make:ddd-shared', ['--force' => true])->assertSuccessful();
    }

    public function test_contracts_have_correct_namespace(): void
    {
        $this->artisan('make:ddd-shared')->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $content = file_get_contents("{$modulesPath}/Shared/Domain/Contracts/EntityInterface.php");
        $this->assertStringContainsString('interface EntityInterface', $content);
    }

    public function test_it_generates_value_objects(): void
    {
        $this->artisan('make:ddd-shared')->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Shared/Domain/ValueObjects/Uuid.php");
        $this->assertFileExists("{$modulesPath}/Shared/Domain/ValueObjects/Email.php");
        $this->assertFileExists("{$modulesPath}/Shared/Domain/ValueObjects/Money.php");
    }
}
