<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class ListDDDModulesCommandTest extends TestCase
{
    public function test_it_lists_no_modules_when_directory_missing(): void
    {
        $this->artisan('ddd:list-modules')->assertSuccessful();
    }

    public function test_it_lists_created_modules(): void
    {
        $this->artisan('make:ddd-module', ['name' => 'Order'])->assertSuccessful();
        $this->artisan('make:ddd-module', ['name' => 'Billing'])->assertSuccessful();

        $this->artisan('ddd:list-modules')
            ->assertSuccessful()
            ->expectsOutputToContain('Order')
            ->expectsOutputToContain('Billing');
    }
}
