<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class MakeDDDModuleCommandTest extends TestCase
{
    public function test_it_creates_a_module(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Order'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertDirectoryExists("{$modulesPath}/Order");
    }

    public function test_it_studly_cases_the_module_name(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'user-order'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertDirectoryExists("{$modulesPath}/UserOrder");
    }

    public function test_it_fails_if_module_already_exists(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Order'])->assertSuccessful();
        $this->artisan('make:ddd-module', ['name' => 'Order'])->assertFailed();
    }

    public function test_it_force_overwrites_existing_module(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Order'])->assertSuccessful();
        $this->artisan('make:ddd-module', ['name' => 'Order', '--force' => true])->assertSuccessful();
    }

    public function test_it_creates_service_provider_stub(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Billing'])->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $providerFile = "{$modulesPath}/Billing/Infrastructure/Providers/BillingServiceProvider.php";
        $this->assertFileExists($providerFile);

        $content = file_get_contents($providerFile);
        $this->assertStringContainsString('BillingServiceProvider', $content);
        $this->assertStringContainsString('class BillingServiceProvider', $content);
    }

    public function test_it_creates_entity_stub_with_correct_namespace(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Inventory'])->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $entityFile = "{$modulesPath}/Inventory/Domain/Entities/InventoryEntity.php";
        $this->assertFileExists($entityFile);

        $content = file_get_contents($entityFile);
        $this->assertStringContainsString('App\\Modules\\Inventory\\Domain\\Entities', $content);
        $this->assertStringContainsString('class InventoryEntity', $content);
    }

    public function test_it_creates_domain_layer(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Shipping'])->assertSuccessful();
        $modulesPath = config('ddd-modules.modules_path');

        $this->assertDirectoryExists("{$modulesPath}/Shipping/Domain/Entities");
        $this->assertDirectoryExists("{$modulesPath}/Shipping/Domain/ValueObjects");
        $this->assertDirectoryExists("{$modulesPath}/Shipping/Domain/Aggregates");
        $this->assertDirectoryExists("{$modulesPath}/Shipping/Domain/Repositories");
        $this->assertDirectoryExists("{$modulesPath}/Shipping/Domain/Services");
        $this->assertDirectoryExists("{$modulesPath}/Shipping/Domain/Events");
    }

    public function test_it_creates_application_layer(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Notification'])->assertSuccessful();
        $modulesPath = config('ddd-modules.modules_path');

        $this->assertDirectoryExists("{$modulesPath}/Notification/Application/UseCases");
        $this->assertDirectoryExists("{$modulesPath}/Notification/Application/DTOs");
        $this->assertDirectoryExists("{$modulesPath}/Notification/Application/Commands");
        $this->assertDirectoryExists("{$modulesPath}/Notification/Application/Queries");
        $this->assertDirectoryExists("{$modulesPath}/Notification/Application/Handlers");
    }

    public function test_it_creates_infrastructure_layer(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Report'])->assertSuccessful();
        $modulesPath = config('ddd-modules.modules_path');

        $this->assertDirectoryExists("{$modulesPath}/Report/Infrastructure/Persistence/Eloquent");
        $this->assertDirectoryExists("{$modulesPath}/Report/Infrastructure/Persistence/Repositories");
        $this->assertDirectoryExists("{$modulesPath}/Report/Infrastructure/Providers");
    }

    public function test_it_creates_presentation_layer(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Dashboard'])->assertSuccessful();
        $modulesPath = config('ddd-modules.modules_path');

        $this->assertDirectoryExists("{$modulesPath}/Dashboard/Presentation/Http/Controllers/Api");
        $this->assertDirectoryExists("{$modulesPath}/Dashboard/Presentation/Http/Controllers/Web");
        $this->assertDirectoryExists("{$modulesPath}/Dashboard/Presentation/Http/Routes");
    }

    public function test_it_only_creates_specified_layers(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Widget', '--only' => 'Domain'])->assertSuccessful();
        $modulesPath = config('ddd-modules.modules_path');

        $this->assertDirectoryExists("{$modulesPath}/Widget/Domain/Entities");
        $this->assertDirectoryDoesNotExist("{$modulesPath}/Widget/Application");
        $this->assertDirectoryDoesNotExist("{$modulesPath}/Widget/Infrastructure");
    }
}
