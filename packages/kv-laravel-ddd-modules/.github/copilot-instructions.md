# Laravel DDD Modules – Copilot Instructions

## Package Overview

- **Package name:** `kv-laravel-ddd-modules`
- **Root namespace:** `LaravelDDD`
- **Architecture:** Domain-Driven Design (DDD) with CQRS support
- **Laravel compatibility:** 10, 11, 12
- **PHP requirement:** ≥ 8.1

---

## Architecture Principles

### Bounded Contexts
Each feature area is a **bounded context** (e.g. `ProductCatalog`, `OrderManagement`).  
All contexts live under the configured `base_path` (default: `app/`).

### Layers (within each context)
| Layer | Directory | Responsibility |
|---|---|---|
| **Domain** | `Domain/` | Business logic, entities, value objects, domain events, repository interfaces, specifications |
| **Application** | `Application/` | Use cases: CQRS commands, queries, handlers, DTOs, application services |
| **Infrastructure** | `Infrastructure/` | Persistence (Eloquent), external services, implementations |
| **Presentation** | `Presentation/` | HTTP controllers, form requests, API resources, routes |

### SharedKernel
Common contracts and value objects shared across contexts are in `SharedKernel/`:
- `Contracts/` – `EntityContract`, `AggregateRootContract`, `RepositoryContract`
- `ValueObjects/` – `Uuid`, `Email`, `Money`

---

## Coding Conventions

1. **`declare(strict_types=1);`** at the top of every PHP file.
2. **`readonly`** classes for value objects, CQRS commands, and CQRS queries.
3. **Constructor property promotion** for all DTOs and value objects.
4. **Full PHPDoc** on all public methods: `@param`, `@return`, `@throws`.
5. **PSR-12** code style enforced via Laravel Pint (`pint.json` uses the `laravel` preset).
6. All value objects must be **immutable** (no setters, use `readonly` or private properties).
7. Use **named arguments** when calling constructors with many parameters.
8. Prefer **`match` expressions** over `switch` statements.

---

## How to Add a New Generator

1. Create a class in `src/Generators/` extending `AbstractGenerator`.
2. Implement `generate(array $options): bool` and `getStubName(): string`.
3. Use `$this->buildNamespace()`, `$this->buildFilePath()`, `$this->buildTokens()` helpers.
4. Create a matching stub file in `stubs/your-stub.stub`.
5. Register the generator in `DddServiceProvider::bindGenerators()`.
6. Create a matching Artisan command in `src/Commands/`.

---

## How to Add a New Artisan Command

1. Create a class in `src/Commands/` extending `Illuminate\Console\Command`.
2. Use `ddd:` prefix for the command signature.
3. Inject the relevant generator(s) via the constructor.
4. Use `$this->info()`, `$this->warn()`, `$this->error()` for output.
5. Return `self::SUCCESS` or `self::FAILURE`.
6. Register in `DddServiceProvider::registerCommands()`.

---

## Configuration

All configuration lives in `config/ddd.php` and is accessible via `config('ddd.*')`.  
Key config values used in code:

| Key | Default | Purpose |
|---|---|---|
| `namespace_root` | `App` | Root PHP namespace for generated classes |
| `base_path` | `app` | Base filesystem directory for contexts |
| `layers` | array | Layer key → directory name mapping |
| `domain_directories` | array | Sub-dirs created in Domain layer |
| `application_directories` | array | Sub-dirs created in Application layer |
| `infrastructure_directories` | array | Sub-dirs created in Infrastructure layer |
| `presentation_directories` | array | Sub-dirs created in Presentation layer |
| `stubs_path` | `null` | Custom stubs override path |
| `auto_discover_contexts` | `true` | Auto-scan for contexts on boot |

---

## Testing

- **Framework:** PHPUnit 10/11 via Orchestra Testbench
- **Base test class:** `LaravelDDD\Tests\TestCase` (extends `Orchestra\Testbench\TestCase`)
- **Unit tests:** `tests/Unit/` – test individual classes in isolation
- **Feature tests:** `tests/Feature/` – test Artisan commands end-to-end using `$this->artisan()`
- Use `sys_get_temp_dir()` for temp directories in tests; always clean up in `tearDown()`.
- Feature tests for generators should assert actual file existence after running commands.

---

## Stub Token Format

Stubs use `{{ tokenName }}` syntax (spaces inside braces are optional).  
Standard tokens available in all stubs:

| Token | Value |
|---|---|
| `{{ className }}` | Generated class name |
| `{{ namespace }}` | Fully-qualified PHP namespace |
| `{{ contextName }}` | Bounded context name |
| `{{ contextNamespace }}` | Context root namespace |
| `{{ domainNamespace }}` | Context Domain namespace |
| `{{ applicationNamespace }}` | Context Application namespace |
| `{{ infrastructureNamespace }}` | Context Infrastructure namespace |
| `{{ presentationNamespace }}` | Context Presentation namespace |
| `{{ year }}` | Current year (YYYY) |
| `{{ date }}` | Current date (Y-m-d) |
