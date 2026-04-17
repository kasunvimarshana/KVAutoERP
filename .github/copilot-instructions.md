# Copilot Instructions

## Project Overview

KVAutoERP is a modular SaaS multi-tenant ERP/CRM platform built with Laravel 13 and PHP 8.3+. It follows Clean Architecture with Domain-Driven Design (DDD), organized as independent modules under `app/Modules/`.

## Architecture

- **Clean Architecture**: Each module has four layers — Domain → Application → Infrastructure (and optional Shared).
- **Domain layer** contains pure PHP classes (entities, repository interfaces, events, exceptions, value objects) with no framework imports.
- **Application layer** defines service contracts and orchestrates domain logic (services, DTOs, use cases).
- **Infrastructure layer** implements persistence (Eloquent models/repositories), HTTP (controllers/resources), and service providers.
- Never import Infrastructure classes in the Domain layer.
- All cross-layer dependencies use interfaces (contracts).

## Module Structure

```
app/Modules/<Module>/
├── Domain/{Entities,RepositoryInterfaces,Events,Exceptions,ValueObjects}
├── Application/{Contracts,Services,DTOs,UseCases}
├── Infrastructure/{Persistence/Eloquent/{Models,Repositories,Traits},Http/{Controllers,Resources,Middleware},Providers,Broadcasting,Services}
├── database/migrations/
├── routes/api.php
└── config/
```

## When Creating a New Module

1. Create the directory structure under `app/Modules/<ModuleName>/` as shown above.
2. Create a `<ModuleName>ServiceProvider` in `Infrastructure/Providers/` that:
   - Binds repository interfaces to Eloquent implementations in `register()`.
   - Loads migrations from `__DIR__.'/../../database/migrations'` in `boot()`.
   - Loads routes from `__DIR__.'/../../../routes/api.php'` in `boot()`.
3. Register the provider in `bootstrap/providers.php`.

## Coding Standards

- Always add `declare(strict_types=1);` to every PHP file.
- Use strong typing for all method parameters and return types.
- Use PHP 8.3+ features: readonly properties, enums, named arguments.
- Models extend `BaseModel` and use `HasUuid`, `HasTenant`, `HasAudit` traits.
- Repositories use `withoutGlobalScopes()` and filter `tenant_id` explicitly.
- Services wrap writes in transactions and dispatch domain events on success.
- Controllers stay thin — delegate to services.
- Use `abs($value) < PHP_FLOAT_EPSILON` for float-zero comparisons.

## Multi-Tenancy

- Tenant isolation uses `HasTenant` trait with a global scope.
- Repositories bypass this scope via `withoutGlobalScopes()` and apply `where('tenant_id', $tenantId)` explicitly.
- The trait auto-fills `tenant_id` on creating if not already set.
- Never hardcode tenant IDs — always derive from auth context or request headers.

## Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Entity | `<Name>.php` | `Product.php` |
| Model | `<Name>Model.php` | `ProductModel.php` |
| Repository | `Eloquent<Name>Repository.php` | `EloquentProductRepository.php` |
| Service | `<Name>Service.php` | `ProductService.php` |
| Controller | `<Name>Controller.php` | `ProductController.php` |
| Resource | `<Name>Resource.php` | `ProductResource.php` |
| Migration | `YYYY_MM_DD_NNNNNN_create_<table>_table.php` | `2026_01_01_000001_create_products_table.php` |
| Namespace | `Modules\<Module>\...` | `Modules\Product\Domain\Entities\Product` |

## Common Patterns

### Domain Entity

```php
declare(strict_types=1);

namespace Modules\<Module>\Domain\Entities;

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

namespace Modules\<Module>\Domain\RepositoryInterfaces;

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

## Key Technologies

- **Laravel Passport** — OAuth2 API authentication.
- **Laravel Reverb** — Real-time WebSocket broadcasting.
- **L5 Swagger** (`darkaonline/l5-swagger`) — OpenAPI/Swagger API documentation.

## What to Avoid

- Do not create circular dependencies between modules.
- Do not import Infrastructure classes in the Domain layer.
- Do not bypass the repository pattern for database access.
- Do not hardcode tenant IDs — always derive from auth context or request headers.
- Do not modify Core module traits without considering impact on all modules.

## Build and Test Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run tests for a specific module
./vendor/bin/phpunit --filter=<ModuleName>

# Run linting
./vendor/bin/pint
```
