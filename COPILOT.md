# Copilot Coding Agent Instructions

## Quick Reference

This is a Laravel-based modular SaaS ERP/CRM platform. All code lives in `app/Modules/`.

## When Creating a New Module

1. Create the directory structure under `app/Modules/<ModuleName>/`:
   - `Domain/Entities/`, `Domain/RepositoryInterfaces/`, `Domain/Events/`, `Domain/Exceptions/`
   - `Application/Contracts/`, `Application/Services/`, `Application/DTOs/`
   - `Infrastructure/Persistence/Eloquent/Models/`, `Infrastructure/Persistence/Eloquent/Repositories/`
   - `Infrastructure/Http/Controllers/`, `Infrastructure/Http/Resources/`
   - `Infrastructure/Providers/`
   - `database/migrations/`, `routes/api.php`, `config/`
2. Create a `<ModuleName>ServiceProvider` in `Infrastructure/Providers/` that:
   - Binds repository interfaces to Eloquent implementations in `register()`
   - Loads migrations from `__DIR__.'/../../database/migrations'` in `boot()`
   - Loads routes from `__DIR__.'/../../../routes/api.php'` in `boot()`
3. Register the provider in `bootstrap/providers.php`.

## When Modifying Existing Code

- Respect the layered architecture: Domain → Application → Infrastructure.
- Never import Infrastructure classes in the Domain layer.
- Always use interfaces for cross-layer dependencies.
- Add `HasUuid`, `HasTenant`, and `HasAudit` traits to new Eloquent models.
- Use `withoutGlobalScopes()` in repository queries and filter `tenant_id` manually.

## Common Patterns

### Domain Entity

```php
declare(strict_types=1);

namespace App\Modules\<Module>\Domain\Entities;

class <Entity>
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        // ... domain properties
    ) {}

    // Domain logic methods here
}
```

### Repository Interface

```php
declare(strict_types=1);

namespace App\Modules\<Module>\Domain\RepositoryInterfaces;

interface <Entity>RepositoryInterface
{
    public function findById(string $tenantId, string $id): ?<Entity>;
    public function findAll(string $tenantId): array;
    public function save(<Entity> $entity): void;
    public function delete(string $tenantId, string $id): void;
}
```

### Service Provider Binding

```php
public function register(): void
{
    $this->app->bind(
        <Entity>RepositoryInterface::class,
        Eloquent<Entity>Repository::class
    );
}
```

## File Naming

- Entities: `<Name>.php` (e.g., `Product.php`)
- Models: `<Name>Model.php` (e.g., `ProductModel.php`)
- Repositories: `Eloquent<Name>Repository.php`
- Services: `<Name>Service.php`
- Controllers: `<Name>Controller.php`
- Resources: `<Name>Resource.php`
- Migrations: `YYYY_MM_DD_NNNNNN_create_<table>_table.php`

## Build and Test Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run tests for a specific module
./vendor/bin/phpunit --filter=<ModuleName>
```
