# KVAutoERP

A modular SaaS multi-tenant ERP/CRM platform built with **Laravel 13** and **PHP 8.3+**, following Clean Architecture principles. The system is organized as independent modules under `app/Modules/`.

## Quick Start

```bash
composer install
cp .env.example .env && php artisan key:generate
./vendor/bin/phpunit          # Tests use SQLite :memory: — no DB setup needed
./vendor/bin/pint             # Lint
npm install --ignore-scripts && npm run build   # Frontend assets (optional)
```

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Framework | Laravel 13 |
| PHP | 8.3+ |
| Auth | Laravel Passport (OAuth2) |
| Real-time | Laravel Reverb (WebSockets) |
| API Docs | L5 Swagger (OpenAPI) |
| Testing | PHPUnit (SQLite :memory:) |
| Autoload | PSR-4: `Modules\\` → `app/Modules/` |

## Module Status

The platform has 19 modules in various stages of implementation:

### Fully Implemented (8 modules — 660 PHP files)

| Module | Files | Description |
|--------|-------|-------------|
| **Product** | 151 | Catalog, variants, categories, brands, UoM, identifiers |
| **User** | 133 | Users, profiles, roles, permissions, devices, attachments |
| **Tenant** | 117 | Multi-tenant management, plans, settings, attachments, config |
| **Finance** | 98 | Double-entry accounting, chart of accounts, fiscal years/periods, journal entries |
| **Auth** | 55 | OAuth2 login/register, SSO, composite RBAC+ABAC authorization |
| **Core** | 46 | Shared kernel: traits, base classes, repository abstractions |
| **OrganizationUnit** | 42 | Hierarchical org structures (materialized path), attachments |
| **Audit** | 18 | Immutable audit log with change tracking |

### Infrastructure-Only (2 modules)

| Module | Description |
|--------|-------------|
| **Configuration** | ServiceProvider + routes file (no domain code) |
| **Shared** | ServiceProvider + routes file + global reference table migration (countries, currencies, languages, timezones) |

### Migration-Only Stubs (9 modules — schema defined, no application code)

Customer, Employee, Supplier, Pricing, Tax, Warehouse, Inventory, Purchase, Sales

## Project Layout

```
app/Modules/           # 19 business modules
bootstrap/providers.php  # 12 ServiceProviders registered
composer.json            # PSR-4 autoload config
phpunit.xml              # Test config (SQLite :memory:)
database/migrations/     # Framework migrations + deferred cross-module FKs
tests/                   # 54 test files (52 tests + 2 framework examples)
```

## Module Architecture

Fully implemented modules follow this layered structure:

```
app/Modules/<Module>/
├── Domain/
│   ├── Entities/            # Pure PHP domain objects
│   ├── RepositoryInterfaces/  # Persistence contracts
│   ├── Events/              # Domain events
│   ├── Exceptions/          # Domain-specific exceptions
│   └── ValueObjects/        # Immutable value types
├── Application/
│   ├── Contracts/           # Service interfaces
│   ├── Services/            # Service implementations
│   └── DTOs/                # Data transfer objects
├── Infrastructure/
│   ├── Persistence/Eloquent/
│   │   ├── Models/          # Eloquent models (extend Model directly)
│   │   └── Repositories/    # Implements Domain repository interfaces
│   ├── Http/
│   │   ├── Controllers/     # Thin controllers delegating to services
│   │   ├── Resources/       # API resources
│   │   ├── Requests/        # Form request validation
│   │   └── Middleware/       # Module-specific middleware
│   ├── Providers/           # ServiceProvider (bindings, migrations, routes)
│   └── Broadcasting/        # Channel definitions (where applicable)
├── database/migrations/     # Module-scoped migrations
└── routes/api.php           # Module API routes
```

## Key Conventions

### Models & Traits

All 26 Eloquent models extend `Illuminate\Database\Eloquent\Model` directly (not `BaseModel`). The `BaseModel` abstract class exists in Core but is currently unused.

- **`HasAudit`**: Used by 20 models for automatic change tracking via the Audit module.
- **`SoftDeletes`**: Used by 8 models (AccountModel, OrgUnitModel, OrgUnitAttachmentModel, ProductModel, TenantModel, TenantAttachmentModel, UserModel, UserAttachmentModel).
- **`HasUuid`**: Defined in Core but currently unused — all models use integer auto-increment primary keys.
- **`HasTenant`**: Defined in Core but currently unused — tenant isolation is handled via middleware and explicit repository filtering.

### Multi-Tenancy

Tenant isolation uses the `resolve.tenant` middleware (`ResolveTenant`) which reads `X-Tenant-ID` from request headers and binds the tenant to the request context. Repositories filter by `tenant_id` explicitly. There is no global scope-based tenant isolation.

### Namespaces

All module code uses `Modules\<Module>\...` (not `App\Modules\...`).

### Coding Standards

- `declare(strict_types=1);` in every PHP file
- Strong typing on all method parameters and return types
- Services wrap writes in DB transactions
- Controllers delegate to services (thin controllers)
- `DECIMAL(20,6)` for monetary values — never `float`
- `abs($value) < PHP_FLOAT_EPSILON` for float-zero comparisons

## API Routes

All authenticated routes require `auth:api` and `resolve.tenant` middleware (except Auth endpoints).

| Module | Prefix | Key Endpoints |
|--------|--------|---------------|
| Auth | `/auth` | register, login, logout, me, refresh, SSO, forgot/reset password |
| Tenant | `/tenants` | CRUD, config update, attachments, plans, settings |
| User | `/users` | CRUD, roles, permissions, profile, devices, attachments |
| OrganizationUnit | `/organization-units` | CRUD, attachments |
| Product | `/products` | CRUD + variants, brands, categories, identifiers, UoM, conversions |
| Finance | `/accounts`, `/fiscal-years`, `/fiscal-periods`, `/journal-entries` | CRUD, journal posting |
| Audit | `/audit-logs` | Read-only (index, show) |

## Registered Providers

```
AppServiceProvider, CoreServiceProvider, ConfigurationServiceProvider,
SharedServiceProvider, AuditServiceProvider, AuthModuleServiceProvider,
TenantServiceProvider, TenantConfigServiceProvider, UserServiceProvider,
OrganizationUnitServiceProvider, ProductServiceProvider, FinanceServiceProvider
```

## Testing

54 test files covering 6 modules:

| Module | Unit Tests | Feature Tests |
|--------|-----------|--------------|
| Finance | AccountService, FiscalYear/Period/JournalEntry services, exception | Routes, endpoints |
| Product | 7 service tests | 14 route/repo/endpoint tests, UoM consistency |
| Audit | Controller, Resource, Service | Routes, endpoints, repository |
| Architecture | Module boundaries, timestamps, guardrails | — |
| Shared | — | Migration smoke test |
| Configuration | — | Migration smoke test |

## Migrations

66 module-scoped migrations + 3 framework migrations in `database/migrations/`.

Cross-module foreign keys are deferred to `database/migrations/2024_01_01_999999_add_remaining_foreign_keys.php`.

Migration naming convention: `{table}_{column(s)}_{type}` with suffixes `_pk`, `_uk`, `_idx`, `_fk`.

## File Naming

| Type | Pattern | Example |
|------|---------|---------|
| Entity | `<Name>.php` | `Product.php` |
| Model | `<Name>Model.php` | `ProductModel.php` |
| Repository | `Eloquent<Name>Repository.php` | `EloquentProductRepository.php` |
| Service | `<Name>Service.php` | `ProductService.php` |
| Controller | `<Name>Controller.php` | `ProductController.php` |

## What to Avoid

- Do not create circular dependencies between modules.
- Do not import Infrastructure classes in the Domain layer.
- Do not bypass the repository pattern for database access.
- Do not hardcode tenant IDs — always derive from auth context or request headers.
- Do not use `float` for monetary or quantity fields — use `DECIMAL(20,6)`.

## License

All rights reserved.
