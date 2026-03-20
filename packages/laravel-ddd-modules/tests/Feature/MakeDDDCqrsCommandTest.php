<?php

namespace LaravelDddModules\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use LaravelDddModules\Tests\TestCase;

class MakeDDDCqrsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $modulesPath = config('ddd-modules.modules_path');
        $files = new Filesystem();
        $files->makeDirectory("{$modulesPath}/Order/Application/Commands", 0755, true, true);
        $files->makeDirectory("{$modulesPath}/Order/Application/Queries", 0755, true, true);
        $files->makeDirectory("{$modulesPath}/Order/Application/Handlers", 0755, true, true);
    }

    public function test_it_creates_a_cqrs_command(): void
    {
        $this->artisan('make:ddd-command', ['module' => 'Order', 'name' => 'CreateOrder'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Application/Commands/CreateOrderCommand.php");
    }

    public function test_it_appends_command_suffix(): void
    {
        $this->artisan('make:ddd-command', ['module' => 'Order', 'name' => 'UpdateOrder'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Application/Commands/UpdateOrderCommand.php");
    }

    public function test_it_creates_a_cqrs_query(): void
    {
        $this->artisan('make:ddd-query', ['module' => 'Order', 'name' => 'GetOrder'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Application/Queries/GetOrderQuery.php");
    }

    public function test_it_creates_a_handler(): void
    {
        $this->artisan('make:ddd-handler', ['module' => 'Order', 'name' => 'CreateOrder'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Application/Handlers/CreateOrderHandler.php");
    }

    public function test_it_fails_if_command_already_exists(): void
    {
        $this->artisan('make:ddd-command', ['module' => 'Order', 'name' => 'CreateOrder'])->assertSuccessful();
        $this->artisan('make:ddd-command', ['module' => 'Order', 'name' => 'CreateOrder'])->assertFailed();
    }
}
