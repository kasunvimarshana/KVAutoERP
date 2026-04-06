# KVAutoERP

An enterprise-grade SaaS multi-tenant ERP/CRM platform built with Laravel, following Clean Architecture and Domain-Driven Design principles.

## Features

- **Multi-Tenant SaaS** — Secure tenant isolation with shared infrastructure
- **Modular Architecture** — Independent, self-contained business modules
- **Clean Architecture** — Strict separation of Domain, Application, and Infrastructure layers
- **Real-Time Communication** — WebSocket broadcasting via Laravel Reverb
- **OAuth2 Authentication** — Secure API access via Laravel Passport
- **API Documentation** — Auto-generated OpenAPI specs via L5 Swagger
- **Audit Trail** — Automatic change tracking on all entities
- **UUID Primary Keys** — Non-sequential, globally unique identifiers

## Architecture

The platform is organized as independent modules under `app/Modules/`, each following a layered architecture:

```
app/Modules/<Module>/
├── Domain/          # Business entities, repository interfaces, events, value objects
├── Application/     # Service contracts, implementations, DTOs, use cases
├── Infrastructure/  # Eloquent models, repositories, controllers, providers
├── database/        # Migrations
├── routes/          # API route definitions
└── config/          # Module configuration
```

### Core Module

The `Core` module provides shared infrastructure used across all other modules:

- **BaseModel** — Abstract Eloquent model with soft deletes and audit support
- **HasUuid** — UUID primary key generation
- **HasTenant** — Multi-tenant scoping
- **HasAudit** — Automatic audit log recording
- **BaseController** — Abstract controller with service injection
- **BaseRepository** — Abstract repository with common query methods
- **BaseService** — Abstract service with transaction and event support
- **Value Objects** — Money, Email, Address, PhoneNumber, Sku, and more
- **Broadcasting** — Tenant-aware and presence-aware channels

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Framework | Laravel |
| Authentication | Laravel Passport (OAuth2) |
| Real-Time | Laravel Reverb (WebSocket) |
| API Docs | L5 Swagger (OpenAPI) |
| Database | MySQL / PostgreSQL |
| Testing | PHPUnit |

## Getting Started

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run tests for a specific module
./vendor/bin/phpunit --filter=<ModuleName>
```

## Project Structure

```
├── app/Modules/           # Business modules (Core, and domain modules)
├── .github/
│   └── copilot-instructions.md  # Copilot custom instructions
├── AGENT.md               # Agent coding instructions
├── CLAUDE.md              # Claude-specific instructions
├── COPILOT.md             # Copilot coding agent instructions
└── README.md              # This file
```

## License

All rights reserved.
