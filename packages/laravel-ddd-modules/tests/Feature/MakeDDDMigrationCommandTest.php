<?php

namespace LaravelDddModules\Tests\Feature;

use LaravelDddModules\Tests\TestCase;

class MakeDDDMigrationCommandTest extends TestCase
{
    public function test_it_creates_a_migration(): void
    {
        $this->artisan('make:ddd-migration', ['module' => 'Order'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $migrationsDir = "{$modulesPath}/Order/Infrastructure/Persistence/Migrations";
        $this->assertDirectoryExists($migrationsDir);

        $files = glob("{$migrationsDir}/*_create_orders_table.php");
        $this->assertNotEmpty($files, 'Migration file was not created');
    }

    public function test_migration_has_correct_table_name(): void
    {
        $this->artisan('make:ddd-migration', ['module' => 'UserProfile'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $migrationsDir = "{$modulesPath}/UserProfile/Infrastructure/Persistence/Migrations";
        $files = glob("{$migrationsDir}/*_create_user_profiles_table.php");
        $this->assertNotEmpty($files, 'Migration for UserProfile was not created');
    }

    public function test_migration_contains_correct_content(): void
    {
        $this->artisan('make:ddd-migration', ['module' => 'Invoice'])
            ->assertSuccessful();

        $modulesPath = config('ddd-modules.modules_path');
        $migrationsDir = "{$modulesPath}/Invoice/Infrastructure/Persistence/Migrations";
        $files = glob("{$migrationsDir}/*_create_invoices_table.php");
        $this->assertNotEmpty($files);

        $content = file_get_contents($files[0]);
        $this->assertStringContainsString('invoices', $content);
        $this->assertStringContainsString('Schema::create', $content);
    }
}
