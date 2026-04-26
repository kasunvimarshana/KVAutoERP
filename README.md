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
|-----------|-----------|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Auth | Laravel Passport (OAuth2) |
| Real-time | Laravel Reverb (WebSockets) |
| API Docs | L5 Swagger (OpenAPI) |
| Testing | PHPUnit (SQLite :memory:) |
| Autoload | PSR-4: `Modules\\` → `app/Modules/` |

## Module Status

### Infrastructure-Only (2 modules)

| Module | Description |
|--------|-------------|
| **Configuration** | Owns global reference data (countries, currencies, languages, timezones) with Domain/Application/Infrastructure layers |
| **Shared** | Minimal module shell (provider + empty route surface; no domain-owned runtime logic) |

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

## Testing

## Migrations

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

## License

All rights reserved.
