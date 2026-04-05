# KVAutoERP

An enterprise-grade, SaaS multi-tenant ERP/CRM platform built with **Laravel** and **PHP 8+**.

## Overview

KVAutoERP is a modular, scalable platform designed for diverse industries including retail, wholesale, manufacturing, eCommerce, pharmacy, healthcare, warehouse logistics, and more. It uses a clean architecture with strict separation of concerns across Domain, Application, Infrastructure, and Shared layers.

### Key Capabilities

- **Multi-Tenancy** — Secure tenant isolation with efficient resource sharing
- **Financial Management & Accounting** — Chart of accounts, journal entries, double-entry accounting, AP/AR, bank accounts, credit cards, Balance Sheet and Profit & Loss reporting
- **Product Management** — Physical, service, digital, combo, and variable products
- **Inventory & Stock Management** — Real-time stock, movements, adjustments, reservations, transfers, reconciliation, batch/lot/serial tracking
- **Warehouse & Location Management** — Multi-location warehouses with hierarchical location structures
- **Order Management** — Sales, purchases, and returns with full ACID-compliant transactions
- **CRM** — Customer and supplier management, contacts, leads, opportunities, activities
- **Pricing & Taxation** — Flexible pricing rules, tax groups with compound support
- **POS** — Point-of-sale terminals, sessions, and transactions
- **Audit & Compliance** — Immutable, append-only audit logs with full audit trails
- **Configuration & Settings** — Organization units, system settings, and module configuration
- **Barcode System** — Generation and scanning supporting all standard barcode types (EAN, UPC, Code128, QR, and more)
- **Currency Management** — Multi-currency support with exchange rate conversion
- **Asset Management** — Fixed asset tracking with depreciation
- **Contract & Maintenance** — Contract management and maintenance scheduling

## Tech Stack

| Component       | Technology                                       |
|-----------------|--------------------------------------------------|
| Language        | PHP 8+ (`declare(strict_types=1)` everywhere)    |
| Framework       | Laravel                                          |
| ORM             | Eloquent                                         |
| Authentication  | Laravel Passport (OAuth2)                        |
| Real-time       | Laravel Reverb (WebSocket broadcasting)          |
| API Docs        | L5 Swagger (OpenAPI)                             |
| Database        | MySQL / PostgreSQL (normalized, minimum 3NF/BCNF)|

## Architecture

The platform follows a modular clean architecture. Every module lives under `app/Modules/<Module>/` with this structure:

```
app/Modules/<Module>/
├── Domain/              # Core business logic (no framework dependencies)
├── Application/         # Use-case orchestration (services, DTOs, contracts)
├── Infrastructure/      # Framework-specific (models, repositories, controllers, providers)
├── Shared/              # Cross-cutting concerns
├── config/              # Module-specific configuration
├── database/migrations/ # Module-specific migrations
└── routes/api.php       # Module API routes
```

### Design Principles

- **SOLID** — Strict single responsibility; depend on abstractions, not concretions
- **DRY / KISS** — No duplication; keep implementations simple and focused
- **Interface-Driven Design** — All services and repositories defined by interfaces, bound via ServiceProviders
- **Repository Pattern** — Domain defines contracts; Infrastructure provides Eloquent implementations
- **Clean Architecture** — Domain is pure; Application orchestrates; Infrastructure adapts

## Getting Started

### Prerequisites

- PHP 8.0+
- Composer
- MySQL or PostgreSQL
- Node.js & npm (for frontend assets, if applicable)

### Installation

```bash
# Clone the repository
git clone https://github.com/kasunvimarshana/KVAutoERP.git
cd KVAutoERP

# Install PHP dependencies
composer install

# Copy environment file and configure
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start the development server
php artisan serve
```

## Testing

```bash
# Run all tests
php artisan test

# Or using PHPUnit directly
./vendor/bin/phpunit
```

## AI Coding Assistant Configuration

This repository includes instruction files for AI coding assistants:

| File | Purpose |
|------|---------|
| [`.github/copilot-instructions.md`](.github/copilot-instructions.md) | Repository-wide Copilot instructions |
| [`COPILOT.md`](COPILOT.md) | Copilot-specific coding conventions |
| [`CLAUDE.md`](CLAUDE.md) | Claude-specific coding conventions |
| [`AGENT.md`](AGENT.md) | Coding agent instructions and boundaries |

## License

See [LICENSE](LICENSE) for details.
