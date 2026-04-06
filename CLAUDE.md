# Claude Code Instructions

## Project Context

KVAutoERP is a modular SaaS multi-tenant ERP/CRM platform built with Laravel. It follows Clean Architecture with DDD, organized as independent modules under `app/Modules/`.

## Architecture Rules

- Each module has four layers: Domain, Application, Infrastructure, and optional Shared.
- Domain layer contains pure PHP classes with no framework imports.
- Application layer defines service contracts and orchestrates domain logic.
- Infrastructure layer implements persistence (Eloquent), HTTP (controllers/resources), and providers.
- Migrations live in `app/Modules/<Module>/database/migrations/`.
- Routes live in `app/Modules/<Module>/routes/api.php`.

## Coding Standards

- Always add `declare(strict_types=1);` to every PHP file.
- Use strong typing for all method parameters and return types.
- Models extend `BaseModel` and use `HasUuid`, `HasTenant`, `HasAudit` traits.
- Repositories use `withoutGlobalScopes()` and filter `tenant_id` explicitly.
- Services wrap writes in transactions and dispatch domain events on success.
- Controllers stay thin — delegate to services.
- Use `abs($value) < PHP_FLOAT_EPSILON` for float-zero comparisons.

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

## Key Technologies

- Laravel Passport for OAuth2 authentication.
- Laravel Reverb for real-time WebSocket broadcasting.
- L5 Swagger for API documentation.

## Multi-Tenancy

Tenant isolation uses `HasTenant` trait with a global scope. Repositories bypass this scope via `withoutGlobalScopes()` and apply `where('tenant_id', $tenantId)` explicitly. The trait auto-fills `tenant_id` on creating if not already set.

## What to Avoid

- Do not create circular dependencies between modules.
- Do not import Infrastructure classes in the Domain layer.
- Do not bypass the repository pattern for database access.
- Do not hardcode tenant IDs — always derive from auth context or request headers.
- Do not modify Core module traits without considering impact on all modules.
