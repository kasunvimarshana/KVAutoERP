# Copilot Instructions for KVAutoERP

## Project Overview

KVAutoERP is an enterprise-grade, SaaS multi-tenant ERP/CRM platform built with **Laravel** and **PHP 8+**. It uses a modular clean architecture with strict separation of concerns across Domain, Application, Infrastructure, and Shared layers.

## Tech Stack

- **Language:** PHP 8+ with `declare(strict_types=1)` in every file
- **Framework:** Laravel
- **ORM:** Eloquent
- **Auth:** Laravel Passport (OAuth2)
- **Real-time:** Laravel Reverb (WebSocket broadcasting)
- **API Docs:** L5 Swagger (OpenAPI)
- **Database:** MySQL/PostgreSQL with normalized schema (minimum 3NF/BCNF)

## Architecture & Module Structure

Every module lives under `app/Modules/<Module>/` and follows this layout:

```
app/Modules/<Module>/
├── Domain/                          # Core business logic (no framework deps)
│   ├── Entities/                    # Immutable domain objects with getters
│   ├── ValueObjects/                # Immutable, self-validating value types
│   ├── Events/                      # Domain events extending BaseEvent
│   ├── Exceptions/                  # Domain-specific exceptions
│   ├── Contracts/Repositories/      # Repository interfaces
│   └── RepositoryInterfaces/        # Additional repository contracts
├── Application/                     # Use-case orchestration
│   ├── Services/                    # Business logic orchestration (extend BaseService)
│   ├── DTOs/                        # Data Transfer Objects (extend BaseDTO)
│   ├── Contracts/                   # Service interfaces
│   └── UseCases/                    # Specific use-case implementations
├── Infrastructure/                  # Framework-specific implementations
│   ├── Persistence/
│   │   └── Eloquent/
│   │       ├── Models/              # Eloquent models (extend BaseModel)
│   │       ├── Repositories/        # Concrete repository implementations
│   │       └── Traits/              # HasTenant, HasUuid, HasAudit, etc.
│   ├── Http/
│   │   ├── Controllers/             # API controllers
│   │   ├── Resources/               # API resources (extend BaseResource)
│   │   └── Middleware/              # HTTP middleware
│   ├── Broadcasting/                # Channels and real-time services
│   ├── Providers/                   # Module ServiceProvider
│   └── Services/                    # Concrete infrastructure services
├── Shared/                          # Cross-cutting concerns
│   ├── Exceptions/
│   └── Helpers/
├── config/                          # Module-specific config files
├── database/
│   └── migrations/                  # Module-specific migrations
└── routes/
    └── api.php                      # Module API routes
```

## Key Conventions

### PHP & Typing
- Always start files with `declare(strict_types=1);`
- Use PHP 8+ features: named arguments, union types, readonly properties, enums
- Use strong type hints on all method parameters and return types
- Use `abs($value) < PHP_FLOAT_EPSILON` for float-zero comparisons instead of `== 0.0`

### Naming
- **Classes:** PascalCase (e.g., `EloquentAuditRepository`, `AuditLogData`)
- **Methods:** camelCase (e.g., `getAuditableId()`, `mapToDomainEntity()`)
- **Constants:** UPPER_SNAKE_CASE (e.g., `CREATED`, `UPDATED`, `DELETED`)
- **Properties:** camelCase (e.g., `protected RepositoryInterface $repository`)
- **Namespaces:** `Modules\<Module>\{Domain|Application|Infrastructure}\<SubCategory>`
- **Files:** Must match class name exactly

### Domain Layer Rules
- Entities are immutable read-only objects with getter methods—no setters
- Value Objects extend `ValueObject`, are immutable, validate on construction
- Domain layer has **zero framework dependencies**
- Repository interfaces live in Domain; implementations live in Infrastructure

### Application Layer Rules
- Services extend `BaseService` and wrap write operations in `DB::transaction()`
- DTOs extend `BaseDTO` with factory methods, validation rules, and array conversion
- Service interfaces live in `Application/Contracts/`

### Infrastructure Layer Rules
- Eloquent models extend `BaseModel` (which includes `SoftDeletes`)
- All tables for models extending `BaseModel` must include `$table->softDeletes()`
- Repositories bypass the `HasTenant` global scope with `withoutGlobalScopes()` and manually filter `where('tenant_id', $tenantId)`
- Controllers extend `BaseController` or `AuthorizedController`
- API resources extend `BaseResource`

### Multi-Tenancy
- Tenant isolation uses `HasTenant` trait with a global scope
- Tenant ID resolves from the authenticated user's `tenant_id` or the `X-Tenant-ID` header
- On model creation, `tenant_id` is auto-filled only when the attribute is empty

### Dependency Flow
```
Controller → Service → Repository Interface → Eloquent Repository → Model → Database
```
Dependencies always flow **inward**: Infrastructure → Application → Domain. Domain has no outward dependencies.

### Service Provider Pattern
- Each module has a `ServiceProvider` in `Infrastructure/Providers/`
- Providers bind interfaces to concrete implementations
- Providers boot with `$this->loadMigrationsFrom(...)` and `$this->loadRoutesFrom(...)`
- All module providers are registered in `bootstrap/providers.php`

### Routing
- Module routes are defined in `<Module>/routes/api.php`
- Routes use `Route::prefix('api')->group(...)` inside the route file
- ServiceProviders load routes via `loadRoutesFrom(.../routes/api.php)`

### Event-Driven Architecture
- Domain events extend `BaseEvent` and support broadcasting
- Events are dispatched after successful database transactions in services
- Guard event dispatching with `app()->bound('events')` when the event dispatcher may not be available

### Auditing
- Use the `HasAudit` trait on models to automatically record changes
- The trait captures before/after snapshots and logs via `AuditService`
- Gracefully handles missing audit service (try-catch on resolution) for testability

## Design Principles

- **SOLID:** Strict single responsibility; depend on abstractions (interfaces), not concretions
- **DRY/KISS:** No duplication; keep implementations simple and focused
- **Interface-Driven Design:** All services and repositories are defined by interfaces, bound via ServiceProviders
- **Repository Pattern:** Domain defines the contract; Infrastructure provides the Eloquent implementation
- **Clean Architecture:** Domain is pure; Application orchestrates; Infrastructure adapts to frameworks

## Database Conventions

- Migrations go in `app/Modules/<Module>/database/migrations/`
- Migration filename format: `YYYY_MM_DD_HHMMSS_create_<table>_table.php`
- Tables must include `$table->softDeletes()` when the model extends `BaseModel`
- Use UUID primary keys via the `HasUuid` trait where applicable
- Normalize to at least 3NF/BCNF
- Hierarchical data uses materialized path pattern (e.g., OrgUnit `path` column)

## Testing

- Tests use PHPUnit following Laravel conventions
- Test files go in the `tests/` directory
- Run tests with `php artisan test` or `./vendor/bin/phpunit`
