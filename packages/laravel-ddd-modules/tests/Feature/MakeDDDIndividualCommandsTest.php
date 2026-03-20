<?php

namespace LaravelDddModules\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use LaravelDddModules\Tests\TestCase;

class MakeDDDIndividualCommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Create base module directory structure for the tests
        $modulesPath = config('ddd-modules.modules_path');
        $fs = new Filesystem();
        $dirs = [
            "{$modulesPath}/Order/Domain/Aggregates",
            "{$modulesPath}/Order/Domain/Events",
            "{$modulesPath}/Order/Domain/Services",
            "{$modulesPath}/Order/Domain/Specifications",
            "{$modulesPath}/Order/Domain/Repositories",
            "{$modulesPath}/Order/Infrastructure/Persistence/Repositories",
            "{$modulesPath}/Order/Application/DTOs",
        ];
        foreach ($dirs as $dir) {
            $fs->makeDirectory($dir, 0755, true, true);
        }
    }

    public function test_make_ddd_aggregate(): void
    {
        $this->artisan('make:ddd-aggregate', ['module' => 'Order', 'name' => 'Order'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Aggregates/OrderAggregate.php");
    }

    public function test_make_ddd_aggregate_fails_if_exists(): void
    {
        $this->artisan('make:ddd-aggregate', ['module' => 'Order', 'name' => 'Order'])->assertSuccessful();
        $this->artisan('make:ddd-aggregate', ['module' => 'Order', 'name' => 'Order'])->assertFailed();
    }

    public function test_make_ddd_aggregate_force_overwrites(): void
    {
        $this->artisan('make:ddd-aggregate', ['module' => 'Order', 'name' => 'Order'])->assertSuccessful();
        $this->artisan('make:ddd-aggregate', ['module' => 'Order', 'name' => 'Order', '--force' => true])->assertSuccessful();
    }

    public function test_make_ddd_domain_event(): void
    {
        $this->artisan('make:ddd-domain-event', ['module' => 'Order', 'name' => 'OrderPlaced'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Events/OrderPlaced.php");
    }

    public function test_make_ddd_domain_service(): void
    {
        $this->artisan('make:ddd-domain-service', ['module' => 'Order', 'name' => 'Pricing'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Services/PricingDomainService.php");
    }

    public function test_make_ddd_specification(): void
    {
        $this->artisan('make:ddd-specification', ['module' => 'Order', 'name' => 'OrderIsActive'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Specifications/OrderIsActiveSpecification.php");
    }

    public function test_make_ddd_dto(): void
    {
        $this->artisan('make:ddd-dto', ['module' => 'Order', 'name' => 'CreateOrder'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Application/DTOs/CreateOrderDTO.php");
    }

    public function test_make_ddd_repository_creates_pair(): void
    {
        $this->artisan('make:ddd-repository', ['module' => 'Order', 'name' => 'Order'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Repositories/OrderRepositoryInterface.php");
        $this->assertFileExists("{$modulesPath}/Order/Infrastructure/Persistence/Repositories/EloquentOrderRepository.php");
    }

    public function test_make_ddd_repository_interface_only(): void
    {
        $this->artisan('make:ddd-repository', ['module' => 'Order', 'name' => 'Product', '--interface-only' => true])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Repositories/ProductRepositoryInterface.php");
        $this->assertFileDoesNotExist("{$modulesPath}/Order/Infrastructure/Persistence/Repositories/EloquentProductRepository.php");
    }
}
