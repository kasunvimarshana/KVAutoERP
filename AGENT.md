# Agent Instructions

## Overview

KVAutoERP is an enterprise SaaS multi-tenant ERP/CRM platform using Laravel with a modular Clean Architecture. All business modules reside in `app/Modules/`.

## Core Architecture

- **Clean Architecture**: Domain → Application → Infrastructure layers per module.
- **Interface-driven**: All cross-layer dependencies use interfaces (contracts).
- **Multi-tenant**: `HasTenant` trait enforces tenant isolation; repositories filter explicitly.
- **Event-driven**: Domain events dispatched via services after successful writes.

## Module Layout

```
app/Modules/<Module>/
├── Domain/          # Entities, repository interfaces, events, exceptions, value objects
├── Application/     # Service contracts, service implementations, DTOs, use cases
├── Infrastructure/  # Eloquent models/repos, controllers, resources, providers
├── database/migrations/
├── routes/api.php
└── config/
```

## Conventions

| Concern | Convention |
|---------|-----------|
| Primary keys | UUID via `HasUuid` trait |
| Tenancy | `HasTenant` trait + explicit `tenant_id` filtering in repos |
| Auditing | `HasAudit` trait for automatic change tracking |
| Repositories | Interface in Domain, Eloquent impl in Infrastructure |
| Services | Contract in Application/Contracts, impl in Application/Services |
| Controllers | Extend `BaseController`; delegate to services |
| Routes | `Route::prefix('api')->group(...)` in module `routes/api.php` |
| Providers | Bind interfaces in `register()`, load migrations/routes in `boot()` |
| Float comparison | `abs($value) < PHP_FLOAT_EPSILON` instead of `== 0.0` |
| PHP version | 8.2+ with strict types, readonly props, enums |

## Key Dependencies

- **laravel/passport** — OAuth2 API authentication
- **laravel/reverb** — Real-time WebSocket broadcasting
- **darkaonline/l5-swagger** — OpenAPI/Swagger API documentation

## Testing

- Use PHPUnit for unit and integration tests.
- Test domain entities and services independently of framework.
- Verify tenant isolation in repository tests.
