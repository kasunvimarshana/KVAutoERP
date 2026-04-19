# Claude Code Instructions

## Project Overview

KVAutoERP is a modular SaaS multi-tenant ERP/CRM platform built with **Laravel 13** and **PHP 8.3+**, organized as independent modules under `app/Modules/`. Currently 8 modules are fully implemented, 2 are infrastructure-only, and 9 have only migration schemas.

## Build & Test Commands

```bash
composer install                          # Always run first
./vendor/bin/phpunit                      # All tests (SQLite :memory:)
./vendor/bin/phpunit --filter=<Module>    # Single module
./vendor/bin/pint                         # Lint
cp .env.example .env && php artisan key:generate  # First-time setup
```

## Module Status

**Fully implemented** (have Domain/Application/Infrastructure code):
Core (46 files), Audit (17), Auth (54), Finance (91), OrganizationUnit (39), Product (142), Tenant (113), User (129)

**Infrastructure-only**: Configuration (2 files), Shared (2 files)

**Migration-only stubs** (schema defined, no application code):
Customer, Employee, Supplier, Pricing, Tax, Warehouse, Inventory, Purchase, Sales

## Module Architecture

```
app/Modules/<Module>/
├── Domain/
│   ├── Entities/              # Pure PHP domain objects
│   ├── RepositoryInterfaces/  # Persistence contracts
│   ├── Events/                # Domain events
│   ├── Exceptions/            # Domain-specific exceptions
│   └── ValueObjects/          # Immutable value types
├── Application/
│   ├── Contracts/             # Service interfaces
│   ├── Services/              # Service implementations
│   └── DTOs/                  # Data transfer objects
├── Infrastructure/
│   ├── Persistence/Eloquent/
│   │   ├── Models/            # Eloquent models (extend Model directly)
│   │   └── Repositories/      # Implements Domain repository interfaces
│   ├── Http/
│   │   ├── Controllers/       # Thin controllers delegating to services
│   │   ├── Resources/         # API resources
│   │   ├── Requests/          # Form request validation
│   │   └── Middleware/         # Module-specific middleware
│   ├── Providers/             # ServiceProvider (bindings, migrations, routes)
│   └── Broadcasting/          # Channel definitions (where applicable)
├── database/migrations/
└── routes/api.php
```

## Key Conventions

- **`declare(strict_types=1);`** in every PHP file. Strong typing on all parameters/returns.
- **Namespaces**: `Modules\<Module>\...` (not `App\Modules\...`).
- **Models**: Extend `Illuminate\Database\Eloquent\Model` directly. `BaseModel` exists in Core but is unused.
- **`HasAudit` trait**: Used by 20 models for automatic change tracking.
- **`SoftDeletes`**: Used by 8 models (Account, OrgUnit, OrgUnitAttachment, Product, Tenant, TenantAttachment, User, UserAttachment).
- **`HasUuid` trait**: Defined in Core but currently unused. All models use integer auto-increment PKs.
- **`HasTenant` trait**: Defined in Core but currently unused. Tenant isolation uses `resolve.tenant` middleware with `X-Tenant-ID` header.
- **Multi-tenancy**: `ResolveTenant` middleware reads `X-Tenant-ID` from request headers. Repositories filter `tenant_id` explicitly.
- **Repositories**: Interface in `Domain/RepositoryInterfaces/`, implementation in `Infrastructure/Persistence/Eloquent/Repositories/`.
- **Services**: Contract in `Application/Contracts/`, implementation in `Application/Services/`. Wrap writes in DB transactions.
- **Controllers**: Stay thin — delegate to services.
- **Monetary values**: `DECIMAL(20,6)` — never `float`.
- **Float comparison**: `abs($value) < PHP_FLOAT_EPSILON` instead of `== 0.0`.

## Registered Providers (bootstrap/providers.php)

AppServiceProvider, CoreServiceProvider, ConfigurationServiceProvider, SharedServiceProvider, AuditServiceProvider, AuthModuleServiceProvider, TenantServiceProvider, TenantConfigServiceProvider, UserServiceProvider, OrganizationUnitServiceProvider, ProductServiceProvider, FinanceServiceProvider

## File Naming

| Type | Pattern | Example |
|------|---------|---------|
| Entity | `<Name>.php` | `Product.php` |
| Model | `<Name>Model.php` | `ProductModel.php` |
| Repository | `Eloquent<Name>Repository.php` | `EloquentProductRepository.php` |
| Service | `<Name>Service.php` | `ProductService.php` |
| Controller | `<Name>Controller.php` | `ProductController.php` |

## Migrations

66 module-scoped migrations + 3 framework migrations. Cross-module FKs deferred to `database/migrations/2024_01_01_999999_add_remaining_foreign_keys.php`. Naming convention: `{table}_{column(s)}_{type}` with suffixes `_pk`, `_uk`, `_idx`, `_fk`.

## What to Avoid

- Do not create circular dependencies between modules.
- Do not import Infrastructure classes in the Domain layer.
- Do not bypass the repository pattern for database access.
- Do not hardcode tenant IDs — derive from auth context or request headers.
- Do not use `float` for monetary or quantity fields — use `DECIMAL(20,6)`.

## Key Dependencies

- **laravel/passport** — OAuth2 API authentication
- **laravel/reverb** — Real-time WebSocket broadcasting
- **darkaonline/l5-swagger** — OpenAPI/Swagger API documentation
- Do not modify Core module traits without considering impact on all modules.