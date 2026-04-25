# KVAutoERP

A modular SaaS multi-tenant ERP/CRM platform built with **Laravel 12** and **PHP 8.2+**, following Clean Architecture principles. The system is organized as independent modules under `app/Modules/`.

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
| --------- | ---------- |
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Auth | Laravel Passport (OAuth2) |
| Real-time | Laravel Reverb (WebSockets) |
| API Docs | L5 Swagger (OpenAPI) |
| Testing | PHPUnit (SQLite :memory:) |
| Autoload | PSR-4: `Modules\\` → `app/Modules/` |

## Module Status

The platform has 19 modules in various stages of implementation:

### Fully Implemented (8 modules — 660 PHP files)

| Module | Files | Description |
| ------ | ----- | ----------- |
| **Product** | 151 | Catalog, variants, categories, brands, UoM, identifiers |
| **User** | 133 | Users, profiles, roles, permissions, devices, attachments |
| **Tenant** | 117 | Multi-tenant management, plans, settings, attachments, config |
| **Finance** | 98 | Double-entry accounting, chart of accounts, fiscal years/periods, journal entries |
| **Auth** | 55 | OAuth2 login/register, SSO, composite RBAC+ABAC authorization |
| **Core** | 44 | Shared kernel: base classes, framework abstractions, repository foundations |
| **OrganizationUnit** | 42 | Hierarchical org structures (materialized path), attachments |
| **Audit** | 18 | Immutable audit log with change tracking |

### Infrastructure-Only (2 modules)

| Module | Description |
| ------ | ----------- |
| **Configuration** | Owns global reference data (countries, currencies, languages, timezones) with Domain/Application/Infrastructure layers |
| **Shared** | Minimal module shell (provider + empty route surface; no domain-owned runtime logic) |

### Migration-Only Stubs (9 modules — schema defined, no application code)

Customer, Employee, Supplier, Pricing, Tax, Warehouse, Inventory, Purchase, Sales

## Project Layout

```text
app/Modules/           # 19 business modules
bootstrap/providers.php  # 12 ServiceProviders registered
composer.json            # PSR-4 autoload config
phpunit.xml              # Test config (SQLite :memory:)
database/migrations/     # Framework migrations + deferred cross-module FKs
tests/                   # 54 test files (52 tests + 2 framework examples)
```

## Module Architecture

Fully implemented modules follow this layered structure:

```text
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

- **`HasAudit`**: Defined in Audit (`Modules\Audit\Infrastructure\Persistence\Eloquent\Traits\HasAudit`) and used by 20 models for change tracking.
- **`SoftDeletes`**: Used by 8 models (AccountModel, OrgUnitModel, OrgUnitAttachmentModel, ProductModel, TenantModel, TenantAttachmentModel, UserModel, UserAttachmentModel).
- **`HasUuid`**: Defined in Core but currently unused — all models use integer auto-increment primary keys.
- **`HasTenant`**: Defined in Tenant (`Modules\Tenant\Infrastructure\Persistence\Eloquent\Traits\HasTenant`) and used by tenant-scoped models.

### Multi-Tenancy

Tenant isolation uses the `resolve.tenant` middleware (`ResolveTenant`) which reads `X-Tenant-ID` from request headers and binds the tenant to the request context. Repositories filter by `tenant_id` explicitly. There is no global scope-based tenant isolation.

### Namespaces

All module code uses `Modules\<Module>\...` (not `App\Modules\...`).

### Coding Standards

- `declare(strict_types=1);` in every PHP file
- Strong typing on all method parameters and return types
- Services wrap writes in DB transactions
- Controllers delegate to services (thin controllers)
- `DECIMAL(20,6)` for monetary values
- `abs($value) < PHP_FLOAT_EPSILON` for float-zero comparisons

## API Routes

All authenticated routes require `auth:api` and `resolve.tenant` middleware (except Auth endpoints).

| Module | Prefix | Key Endpoints |
| ------ | ------ | ------------- |
| Auth | `/auth` | register, login, logout, me, refresh, SSO, forgot/reset password |
| Tenant | `/tenants` | CRUD, config update, attachments, plans, settings |
| User | `/users` | CRUD, roles, permissions, profile, devices, attachments |
| OrganizationUnit | `/organization-units` | CRUD, attachments |
| Product | `/products` | CRUD + variants, brands, categories, identifiers, UoM, conversions |
| Finance | `/accounts`, `/fiscal-years`, `/fiscal-periods`, `/journal-entries` | CRUD, journal posting |
| Audit | `/audit-logs` | Read-only (index, show) |

## Registered Providers

```text
AppServiceProvider, CoreServiceProvider, ConfigurationServiceProvider,
SharedServiceProvider, AuditServiceProvider, AuthModuleServiceProvider,
TenantServiceProvider, TenantConfigServiceProvider, UserServiceProvider,
OrganizationUnitServiceProvider, ProductServiceProvider, FinanceServiceProvider
```

## Testing

54 test files covering 6 modules:

| Module | Unit Tests | Feature Tests |
| ------ | ---------- | ------------- |
| Finance | AccountService, FiscalYear/Period/JournalEntry services, exception | Routes, endpoints |
| Product | 7 service tests | 14 route/repo/endpoint tests, UoM consistency |
| Audit | Controller, Resource, Service | Routes, endpoints, repository |
| Architecture | Module boundaries, timestamps, guardrails | — |
| Shared | — | Thin-module guardrails + migration smoke test |
| Configuration | — | Reference-data migration and architecture guardrails |

### Search Benchmarking

Product catalog search latency can be measured with both a runtime command and a test harness.

1. Runtime benchmark command (uses existing tenant data):

```bash
php artisan product:benchmark-search --tenant_id=<TENANT_ID> --warehouse_id=<WAREHOUSE_ID> --iterations=5 --per_page=25 --term="SKU-001" --term="BARCODE-001"
```

1. Performance-tagged test harness (deterministic seeded dataset):

```bash
php ./vendor/bin/phpunit --group performance
```

1. Typical CI check for command behavior:

```bash
php ./vendor/bin/phpunit tests/Feature/ProductSearchBenchmarkCommandTest.php
```

Notes:

- The command prints `min/avg/p95/max` latency in milliseconds per search term.
- Use `--format=json` for machine-readable output in CI/CD dashboards.
- If the configured database is unavailable, the command exits gracefully with a clear diagnostic message.
- For consistent trend tracking, run command benchmarks against stable staging data and fixed `--iterations` values.
- Use the operational playbook in `docs/PRODUCT_SEARCH_PERFORMANCE_RUNBOOK.md` for thresholds, release comparisons, and incident handling.
- Use `docs/PRODUCT_SEARCH_BENCHMARK_REPORT_TEMPLATE.md` to record benchmark outputs in release notes.
- A GitHub Actions gate example is available at `.github/workflows/product-search-benchmark-gate.yml`.
- A lightweight PR contract-check workflow is available at `.github/workflows/product-search-benchmark-pr-check.yml`.
- The benchmark gate can auto-run on `release/**` branches when benchmark repository variables are configured.
- A non-blocking main-branch trend report workflow is available at `.github/workflows/product-search-benchmark-main-report.yml`.

Benchmark workflow matrix:

| Workflow | Trigger | Blocking | Purpose |
| -------- | ------- | -------- | ------- |
| `.github/workflows/product-search-benchmark-pr-check.yml` | Pull requests | Yes (contract checks) | Verify benchmark tests and JSON output contract |
| `.github/workflows/product-search-benchmark-main-report.yml` | `main` pushes + manual | No | Continuous benchmark trend visibility with warnings |
| `.github/workflows/product-search-benchmark-gate.yml` | `release/**` pushes + manual | Yes (threshold gate) | Enforce release-over-release latency regression policy |

Benchmark artifact naming and retrieval:

1. PR checks upload artifacts named `product-search-benchmark-pr-<run_id>`.
1. Main reports upload artifacts named `product-search-benchmark-main-<run_id>`.
1. Release gate runs upload artifacts named `product-search-benchmark-gate-<run_id>`.
1. Open the GitHub Actions run, then download artifacts from the run summary page.
1. Artifact retention follows workflow defaults or `BENCHMARK_ARTIFACT_RETENTION_DAYS` when configured.

Release benchmark go/no-go checklist:

1. Confirm PR contract checks passed in `.github/workflows/product-search-benchmark-pr-check.yml`.
1. Run release benchmark gate using `.github/workflows/product-search-benchmark-gate.yml` with the target baseline file.
1. Review p95 and max deltas term-by-term.
1. Go if no threshold breach is reported.
1. No-go if regression threshold is breached; investigate before promoting release.

## Migrations

66 module-scoped migrations + 3 framework migrations in `database/migrations/`.

Cross-module foreign keys are deferred to `database/migrations/2024_01_01_999999_add_remaining_foreign_keys.php`.

Migration naming convention: `{table}_{column(s)}_{type}` with suffixes `_pk`, `_uk`, `_idx`, `_fk`.

## File Naming

| Type | Pattern | Example |
| ---- | ------- | ------- |
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

## License

All rights reserved.
