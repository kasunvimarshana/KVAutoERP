# Copilot Instructions

## Project Overview

KVAutoERP is a modular SaaS multi-tenant ERP/CRM platform built with Laravel. It follows Clean Architecture with DDD principles. All business modules reside in `app/Modules/`.

## Architecture

Each module follows a strict layered architecture: **Domain → Application → Infrastructure**.

```
app/Modules/<Module>/
├── Domain/                # Entities, RepositoryInterfaces, Events, Exceptions, ValueObjects
├── Application/           # Contracts (service interfaces), Services, DTOs, UseCases
├── Infrastructure/
│   ├── Persistence/Eloquent/Models/       # Eloquent models
│   ├── Persistence/Eloquent/Repositories/ # Repository implementations
│   ├── Http/Controllers/
│   ├── Http/Resources/
│   └── Providers/         # ServiceProvider (binds interfaces, loads routes/migrations)
├── database/migrations/
├── routes/api.php
└── config/
```

### Layer Rules

- **Domain** has no framework imports — pure PHP only.
- **Application** depends only on Domain contracts.
- **Infrastructure** implements Domain/Application interfaces using Eloquent/Laravel.
- Cross-module communication uses events only — never direct class imports between modules.

## Key Technologies

- **laravel/passport** — OAuth2 API authentication
- **laravel/reverb** — Real-time WebSocket broadcasting
- **darkaonline/l5-swagger** — OpenAPI/Swagger API documentation

## Coding Standards

- Always add `declare(strict_types=1);` to every PHP file.
- Use strong typing for all method parameters and return types.
- PHP 8.3+ features: readonly properties, enums, named arguments.
- Use `abs($value) < PHP_FLOAT_EPSILON` for float-zero comparisons, never `== 0.0`.

## Namespaces

Module code uses `Modules\<Module>\...` (not `App\Modules\...`), mapped via PSR-4 in `composer.json`:

```json
"Modules\\": "app/Modules/"
```

## Multi-Tenancy

- `HasTenant` trait adds a global scope filtering by `tenant_id` (from auth user or `X-Tenant-ID` header).
- Repositories **must** call `withoutGlobalScopes()` and filter `tenant_id` explicitly via `where('tenant_id', $tenantId)`.
- The trait auto-fills `tenant_id` on model creation if not already set.
- Never hardcode tenant IDs — always derive from auth context or request headers.

## Eloquent Models

All Eloquent models must:
- Extend `BaseModel` from the Core module.
- Use `HasUuid` trait (UUID primary keys, non-incrementing, string key type).
- Use `HasTenant` trait for tenant-scoped tables.
- Use `HasAudit` trait for automatic change tracking (where applicable).
- Suffix the class name with `Model` (e.g., `ProductModel.php`).

## Repository Pattern

- Define interfaces in `Domain/RepositoryInterfaces/`.
- Implement with Eloquent in `Infrastructure/Persistence/Eloquent/Repositories/`.
- Always use `withoutGlobalScopes()` in repository queries and filter `tenant_id` manually.
- Bind interface → implementation in the module's ServiceProvider `register()` method.

## Services

- Define contracts in `Application/Contracts/`.
- Implement in `Application/Services/`.
- Wrap all write operations in database transactions.
- Dispatch domain events on success.
- Controllers must stay thin — delegate all logic to services.

## Creating a New Module

1. Create directory structure under `app/Modules/<ModuleName>/` as shown above.
2. Create a `<ModuleName>ServiceProvider` in `Infrastructure/Providers/` that:
   - Binds repository interfaces to Eloquent implementations in `register()`.
   - Loads migrations from `__DIR__ . '/../../database/migrations'` in `boot()`.
   - Loads routes from `__DIR__ . '/../../routes/api.php'` in `boot()`.
3. Register the provider in `bootstrap/providers.php`.

## File Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Entity | `<Name>.php` | `Product.php` |
| Model | `<Name>Model.php` | `ProductModel.php` |
| Repository | `Eloquent<Name>Repository.php` | `EloquentProductRepository.php` |
| Service | `<Name>Service.php` | `ProductService.php` |
| Controller | `<Name>Controller.php` | `ProductController.php` |
| Resource | `<Name>Resource.php` | `ProductResource.php` |
| Migration | `YYYY_MM_DD_NNNNNN_create_<table>_table.php` | `2026_04_01_000001_create_products_table.php` |

## Database Standards

- All tables normalized to minimum 3NF/BCNF.
- Primary keys: UUID (`CHAR(36)`), non-incrementing.
- All tenant-scoped tables include `tenant_id` as a foreign key.
- Monetary values: `DECIMAL(20,6)` — never use `FLOAT`.
- Quantity values: `DECIMAL(20,6)` to support fractional units.
- All tables include `created_at` and `updated_at` timestamps.
- Hierarchical data uses adjacency list with `parent_id` and materialized `path`.

## Build & Test Commands

```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/phpunit

# Run tests for a specific module
./vendor/bin/phpunit --filter=<ModuleName>

# Lint with Pint
./vendor/bin/pint

# Full setup (install, env, key, migrate, npm)
composer setup
```

## What to Avoid

- Do not create circular dependencies between modules.
- Do not import Infrastructure classes in the Domain layer.
- Do not bypass the repository pattern for database access.
- Do not hardcode tenant IDs.
- Do not modify Core module traits without considering the impact on all modules.
- Do not use `FLOAT` for monetary or quantity values.
