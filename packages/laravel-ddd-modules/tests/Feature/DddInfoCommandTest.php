<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class DddInfoCommandTest extends TestCase
{
    public function test_ddd_info_runs_successfully(): void
    {
        $this->artisan('ddd:info')->assertSuccessful();
    }

    public function test_ddd_info_shows_config(): void
    {
        $this->artisan('ddd:info')
            ->assertSuccessful()
            ->expectsOutputToContain('Modules Path');
    }
}
