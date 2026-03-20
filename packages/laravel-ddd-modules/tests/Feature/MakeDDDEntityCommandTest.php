<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class MakeDDDEntityCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Create a module first
        $modulesPath = config('ddd-modules.modules_path');
        mkdir("{$modulesPath}/Order/Domain/Entities", 0755, true);
    }

    public function test_it_creates_an_entity(): void
    {
        $this->artisan('make:ddd-entity', ['module' => 'Order', 'name' => 'LineItem'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $this->assertFileExists("{$modulesPath}/Order/Domain/Entities/LineItem.php");
    }

    public function test_it_fails_if_entity_already_exists(): void
    {
        $this->artisan('make:ddd-entity', ['module' => 'Order', 'name' => 'LineItem'])->assertSuccessful();
        $this->artisan('make:ddd-entity', ['module' => 'Order', 'name' => 'LineItem'])->assertFailed();
    }

    public function test_it_force_overwrites_existing_entity(): void
    {
        $this->artisan('make:ddd-entity', ['module' => 'Order', 'name' => 'LineItem'])->assertSuccessful();
        $this->artisan('make:ddd-entity', ['module' => 'Order', 'name' => 'LineItem', '--force' => true])->assertSuccessful();
    }
}
