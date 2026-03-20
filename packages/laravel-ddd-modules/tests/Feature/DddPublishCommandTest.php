<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class DddPublishCommandTest extends TestCase
{
    public function test_ddd_publish_runs_successfully(): void
    {
        $this->artisan('ddd:publish')->assertSuccessful();
    }

    public function test_ddd_publish_stubs_only(): void
    {
        $this->artisan('ddd:publish', ['--stubs' => true])->assertSuccessful();
    }

    public function test_ddd_publish_config_only(): void
    {
        $this->artisan('ddd:publish', ['--config' => true])->assertSuccessful();
    }
}
