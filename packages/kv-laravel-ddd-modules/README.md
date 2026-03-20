# Laravel DDD Modules

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%20|%2011%20|%2012-red)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

A production-ready, zero-dependency Laravel package for building applications with **Domain-Driven Design (DDD)** and **CQRS** support.

Every structural aspect — layer names, directory trees, namespace roots, base paths, stub templates, and provider patterns — is driven entirely by a published and overridable configuration file.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Publishing Configuration](#publishing-configuration)
- [Configuration Reference](#configuration-reference)
- [Architecture Overview](#architecture-overview)
- [Generated Directory Structure](#generated-directory-structure)
- [Artisan Commands](#artisan-commands)
  - [ddd:make-context](#dddmake-context)
  - [ddd:make-entity](#dddmake-entity)
  - [ddd:make-value-object](#dddmake-value-object)
  - [ddd:make-aggregate](#dddmake-aggregate)
  - [ddd:make-event](#dddmake-event)
  - [ddd:make-service](#dddmake-service)
  - [ddd:make-specification](#dddmake-specification)
  - [ddd:make-repository](#dddmake-repository)
  - [ddd:make-command](#dddmake-command)
  - [ddd:make-query](#dddmake-query)
  - [ddd:make-dto](#dddmake-dto)
  - [ddd:list-contexts](#dddlist-contexts)
  - [ddd:publish-stubs](#dddpublish-stubs)
  - [ddd:info](#dddinfo)
- [Stub Token Reference](#stub-token-reference)
- [Customization Guide](#customization-guide)
- [Auto-Discovery](#auto-discovery)
- [SharedKernel](#sharedkernel)
- [Facade](#facade)
- [Extension Patterns](#extension-patterns)
- [End-to-End Example](#end-to-end-example)
- [Testing](#testing)
- [FAQ](#faq)
- [License](#license)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ≥ 8.1 |
| Laravel / Illuminate | 10.x, 11.x, or 12.x |

No third-party module libraries are required.

---

## Installation

Install the package via Composer:

```bash
composer require kasunvimarshana/kv-laravel-ddd-modules
```

### Auto-Discovery

Laravel's package auto-discovery registers the service provider and `Ddd` facade automatically. No manual setup is needed for most applications.

### Manual Registration (optional)

If your application has auto-discovery disabled, add the following to `config/app.php`:

```php
'providers' => [
    // ...
    LaravelDDD\DddServiceProvider::class,
],

'aliases' => [
    // ...
    'Ddd' => LaravelDDD\Facades\Ddd::class,
],
```

---

## Quick Start

> Five minutes to your first bounded context.

**1. Publish the configuration file:**

```bash
php artisan vendor:publish --tag=ddd-config
```

**2. Create a bounded context:**

```bash
php artisan ddd:make-context ProductCatalog
```

This scaffolds the full four-layer directory structure and creates a `ProductCatalogServiceProvider`.

**3. Create a domain entity:**

```bash
php artisan ddd:make-entity ProductCatalog Product
```

**4. Create a value object:**

```bash
php artisan ddd:make-value-object ProductCatalog ProductName
```

**5. Create a repository (interface + Eloquent implementation):**

```bash
php artisan ddd:make-repository ProductCatalog Product
```

**6. Create a CQRS command/handler pair:**

```bash
php artisan ddd:make-command ProductCatalog CreateProduct
```

**7. Create a CQRS query/handler pair:**

```bash
php artisan ddd:make-query ProductCatalog GetProduct
```

**8. List all discovered contexts:**

```bash
php artisan ddd:list-contexts
```

**9. View package info and configuration:**

```bash
php artisan ddd:info
```

---

## Publishing Configuration

Publish the configuration file to your application's `config/` directory:

```bash
php artisan vendor:publish --tag=ddd-config
```

This creates `config/ddd.php` where you can override every default.

To publish stubs for customization:

```bash
php artisan ddd:publish-stubs
```

Or equivalently via Artisan publish:

```bash
php artisan vendor:publish --tag=ddd-stubs
```

Stubs are published to `stubs/ddd/` in your application root.

---

## Configuration Reference

All configuration lives in `config/ddd.php` and is accessible via `config('ddd.*')`.

```php
return [
    // Root PHP namespace for generated classes.
    // Default: 'App'
    'namespace_root' => 'App',

    // Base directory (relative to Laravel app root) where contexts live.
    // Default: 'app'
    'base_path' => 'app',

    // Architecture mode. Currently supports 'ddd'.
    'architecture_mode' => 'ddd',

    // Mapping of layer keys to directory names.
    // You can rename any layer directory here.
    'layers' => [
        'domain'         => 'Domain',
        'application'    => 'Application',
        'infrastructure' => 'Infrastructure',
        'presentation'   => 'Presentation',
    ],

    // Sub-directories created inside the Domain layer.
    'domain_directories' => [
        'Entities',
        'ValueObjects',
        'Repositories',
        'Events',
        'Services',
        'Specifications',
        'Exceptions',
    ],

    // Sub-directories created inside the Application layer.
    'application_directories' => [
        'Commands',
        'Queries',
        'Handlers',
        'DTOs',
        'Services',
    ],

    // Sub-directories created inside the Infrastructure layer.
    'infrastructure_directories' => [
        'Persistence',
        'Repositories',
        'Services',
        'Http',
        'Jobs',
        'Notifications',
    ],

    // Sub-directories created inside the Presentation layer.
    'presentation_directories' => [
        'Http/Controllers',
        'Http/Requests',
        'Http/Resources',
        'Routes',
        'Console',
    ],

    // Directory name for the SharedKernel (relative to base_path).
    'shared_kernel_path' => 'SharedKernel',

    // When true, the package automatically discovers bounded contexts on boot.
    'auto_discover_contexts' => true,

    // When true, each discovered context's ServiceProvider is automatically registered.
    'auto_register_providers' => true,

    // Absolute path to a directory containing custom stub overrides.
    // Set to null to use the package's built-in stubs.
    'stubs_path' => null,

    // Permissions applied when creating directories and files.
    'file_permissions' => [
        'directories' => 0755,
        'files'       => 0644,
    ],
];
```

### Key Configuration Options

| Key | Default | Description |
|---|---|---|
| `namespace_root` | `App` | Root PHP namespace for all generated classes |
| `base_path` | `app` | Filesystem base directory for all contexts |
| `layers` | array | Layer key → directory name mapping |
| `domain_directories` | array | Sub-directories created in the Domain layer |
| `application_directories` | array | Sub-directories created in the Application layer |
| `infrastructure_directories` | array | Sub-directories created in the Infrastructure layer |
| `presentation_directories` | array | Sub-directories created in the Presentation layer |
| `shared_kernel_path` | `SharedKernel` | Directory for the shared kernel |
| `auto_discover_contexts` | `true` | Enable/disable automatic context discovery on boot |
| `auto_register_providers` | `true` | Enable/disable automatic ServiceProvider registration |
| `stubs_path` | `null` | Path to custom stubs directory (overrides built-in stubs) |
| `file_permissions.directories` | `0755` | Directory creation permissions |
| `file_permissions.files` | `0644` | File creation permissions |

---

## Architecture Overview

This package implements a **Domain-Driven Design (DDD)** architecture organized into **bounded contexts**. Each bounded context maps to a self-contained feature area of your application (e.g. `ProductCatalog`, `OrderManagement`, `Identity`).

### Bounded Contexts

Each bounded context contains four layers:

| Layer | Directory | Responsibility |
|---|---|---|
| **Domain** | `Domain/` | Pure business logic: entities, value objects, domain events, repository interfaces, domain services, specifications |
| **Application** | `Application/` | Use-case orchestration: CQRS commands, queries, handlers, DTOs, application services |
| **Infrastructure** | `Infrastructure/` | Technical implementation: Eloquent repositories, external services, jobs, notifications |
| **Presentation** | `Presentation/` | Delivery layer: HTTP controllers, form requests, API resources, routes, console commands |

### SharedKernel

Common contracts and value objects shared across all contexts live in `SharedKernel/`:

- `Contracts/` — `AggregateRootContract`, `EntityContract`, `RepositoryContract`
- `ValueObjects/` — `Uuid`, `Email`, `Money`

> The SharedKernel directory is created automatically the first time you run `ddd:make-context`.

---

## Generated Directory Structure

Running `php artisan ddd:make-context ProductCatalog` produces:

```
app/
├── SharedKernel/                          # Created on first context scaffold
│   ├── Contracts/
│   └── ValueObjects/
│
└── ProductCatalog/
    ├── Domain/
    │   ├── Entities/
    │   ├── ValueObjects/
    │   ├── Repositories/
    │   ├── Events/
    │   ├── Services/
    │   ├── Specifications/
    │   └── Exceptions/
    ├── Application/
    │   ├── Commands/
    │   ├── Queries/
    │   ├── Handlers/
    │   ├── DTOs/
    │   └── Services/
    ├── Infrastructure/
    │   ├── Persistence/
    │   ├── Repositories/
    │   ├── Services/
    │   ├── Http/
    │   ├── Jobs/
    │   └── Notifications/
    └── Presentation/
        ├── Http/
        │   ├── Controllers/
        │   ├── Requests/
        │   └── Resources/
        ├── Routes/
        └── Console/
```

Each context also receives a `ProductCatalogServiceProvider.php` at its root, ready to bind repository interfaces to their implementations and load routes or migrations.

---

## Artisan Commands

All commands share a `--force` flag that overwrites existing files without confirmation.

---

### `ddd:make-context`

Scaffold a complete bounded context with full directory structure and a `ServiceProvider`.

```bash
php artisan ddd:make-context {name} [--force]
```

| Argument | Description |
|---|---|
| `name` | PascalCase context name (e.g. `ProductCatalog`) |

**Options:**

| Option | Description |
|---|---|
| `--force` | Overwrite existing files |

**Examples:**

```bash
php artisan ddd:make-context ProductCatalog
php artisan ddd:make-context OrderManagement --force
```

**What gets created:**

- All four layer directories with their sub-directories
- `app/ProductCatalog/ProductCatalogServiceProvider.php`
- `app/SharedKernel/` (only if it does not exist yet)

---

### `ddd:make-entity`

Generate a Domain Entity class implementing `EntityContract`.

```bash
php artisan ddd:make-entity {context} {name} [--force]
```

| Argument | Description |
|---|---|
| `context` | Bounded context name (e.g. `ProductCatalog`) |
| `name` | Entity class name (e.g. `Product`) |

**Example:**

```bash
php artisan ddd:make-entity ProductCatalog Product
```

**Generated file:** `app/ProductCatalog/Domain/Entities/Product.php`

**Generated class:**

```php
class Product implements EntityContract
{
    public function __construct(private readonly mixed $id) {}

    public function getId(): mixed { ... }
    public function equals(EntityContract $other): bool { ... }
}
```

---

### `ddd:make-value-object`

Generate an immutable Domain Value Object.

```bash
php artisan ddd:make-value-object {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-value-object ProductCatalog ProductName
```

**Generated file:** `app/ProductCatalog/Domain/ValueObjects/ProductName.php`

The generated class is `final`, uses a `readonly` property, includes validation, `equals()`, and `__toString()`.

---

### `ddd:make-aggregate`

Generate a Domain Aggregate Root implementing `AggregateRootContract`.

```bash
php artisan ddd:make-aggregate {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-aggregate ProductCatalog ProductAggregate
```

**Generated file:** `app/ProductCatalog/Domain/Entities/ProductAggregate.php`

---

### `ddd:make-event`

Generate a Domain Event class.

```bash
php artisan ddd:make-event {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-event ProductCatalog ProductCreated
```

**Generated file:** `app/ProductCatalog/Domain/Events/ProductCreated.php`

---

### `ddd:make-service`

Generate a Domain Service class.

```bash
php artisan ddd:make-service {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-service ProductCatalog ProductPricingService
```

**Generated file:** `app/ProductCatalog/Domain/Services/ProductPricingService.php`

---

### `ddd:make-specification`

Generate a Domain Specification class.

```bash
php artisan ddd:make-specification {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-specification ProductCatalog ProductIsAvailable
```

**Generated file:** `app/ProductCatalog/Domain/Specifications/ProductIsAvailable.php`

---

### `ddd:make-repository`

Generate a **Repository Interface** (in the Domain layer) and an **Eloquent Implementation** (in the Infrastructure layer) as a pair.

```bash
php artisan ddd:make-repository {context} {name} [--force]
```

| Argument | Description |
|---|---|
| `context` | Bounded context name |
| `name` | Repository base name (e.g. `Product`) |

**Example:**

```bash
php artisan ddd:make-repository ProductCatalog Product
```

**Generated files:**

| File | Description |
|---|---|
| `app/ProductCatalog/Domain/Repositories/ProductRepositoryInterface.php` | Repository contract |
| `app/ProductCatalog/Infrastructure/Repositories/EloquentProductRepository.php` | Eloquent implementation |

---

### `ddd:make-command`

Generate a **CQRS Command DTO** and its **Command Handler** as a pair.

```bash
php artisan ddd:make-command {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-command ProductCatalog CreateProduct
```

**Generated files:**

| File | Description |
|---|---|
| `app/ProductCatalog/Application/Commands/CreateProductCommand.php` | Command DTO (`readonly` class) |
| `app/ProductCatalog/Application/Handlers/CreateProductCommandHandler.php` | Handler class |

---

### `ddd:make-query`

Generate a **CQRS Query DTO** and its **Query Handler** as a pair.

```bash
php artisan ddd:make-query {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-query ProductCatalog GetProduct
```

**Generated files:**

| File | Description |
|---|---|
| `app/ProductCatalog/Application/Queries/GetProductQuery.php` | Query DTO (`readonly` class) |
| `app/ProductCatalog/Application/Handlers/GetProductQueryHandler.php` | Handler class |

---

### `ddd:make-dto`

Generate an Application **Data Transfer Object** (DTO).

```bash
php artisan ddd:make-dto {context} {name} [--force]
```

**Example:**

```bash
php artisan ddd:make-dto ProductCatalog ProductData
```

**Generated file:** `app/ProductCatalog/Application/DTOs/ProductDataDTO.php`

---

### `ddd:list-contexts`

List all auto-discovered bounded contexts with their layer presence status.

```bash
php artisan ddd:list-contexts
```

**Example output:**

```
+----------------+----------------------------+------------+-----------------+-------------------+
| Context Name   | Path                       | Has Domain | Has Application | Has Infrastructure |
+----------------+----------------------------+------------+-----------------+-------------------+
| ProductCatalog | /app/app/ProductCatalog    | ✓          | ✓               | ✓                 |
| OrderManagement| /app/app/OrderManagement   | ✓          | ✓               | ✓                 |
+----------------+----------------------------+------------+-----------------+-------------------+
Found 2 context(s).
```

---

### `ddd:publish-stubs`

Publish all package stubs to `stubs/ddd/` in your application root so you can customize them.

```bash
php artisan ddd:publish-stubs [--force]
```

| Option | Description |
|---|---|
| `--force` | Overwrite already-published stubs |

After publishing, set `stubs_path` in `config/ddd.php` to `base_path('stubs/ddd')` to activate your custom stubs:

```php
'stubs_path' => base_path('stubs/ddd'),
```

---

### `ddd:info`

Display package information, active configuration, registered contexts, and the full command list.

```bash
php artisan ddd:info
```

---

## Stub Token Reference

All stub files use `{{ tokenName }}` syntax (spaces inside the braces are optional). The following tokens are automatically available in every stub:

| Token | Example Value | Description |
|---|---|---|
| `{{ className }}` | `Product` | Generated class name |
| `{{ namespace }}` | `App\ProductCatalog\Domain\Entities` | Fully-qualified PHP namespace |
| `{{ contextName }}` | `ProductCatalog` | Bounded context name |
| `{{ contextKebab }}` | `product-catalog` | Context name in kebab-case |
| `{{ classSnake }}` | `product` | Class name in snake_case |
| `{{ classCamel }}` | `product` | Class name in camelCase |
| `{{ contextNamespace }}` | `App\ProductCatalog` | Context root namespace |
| `{{ domainNamespace }}` | `App\ProductCatalog\Domain` | Context Domain namespace |
| `{{ applicationNamespace }}` | `App\ProductCatalog\Application` | Context Application namespace |
| `{{ infrastructureNamespace }}` | `App\ProductCatalog\Infrastructure` | Context Infrastructure namespace |
| `{{ presentationNamespace }}` | `App\ProductCatalog\Presentation` | Context Presentation namespace |
| `{{ year }}` | `2026` | Current year (YYYY) |
| `{{ date }}` | `2026-03-20` | Current date (Y-m-d) |

---

## Customization Guide

### Publishing Stubs

```bash
php artisan ddd:publish-stubs
```

Stubs are copied to `stubs/ddd/` in your project root:

```
stubs/
└── ddd/
    ├── entity.stub
    ├── value-object.stub
    ├── aggregate-root.stub
    ├── domain-event.stub
    ├── domain-service.stub
    ├── specification.stub
    ├── repository-interface.stub
    ├── eloquent-repository.stub
    ├── cqrs-command.stub
    ├── cqrs-command-handler.stub
    ├── cqrs-query.stub
    ├── cqrs-query-handler.stub
    ├── dto.stub
    └── context-provider.stub
```

### Activating Custom Stubs

After editing your stubs, tell the package where to find them:

```php
// config/ddd.php
'stubs_path' => base_path('stubs/ddd'),
```

The package always prefers stubs found in `stubs_path` over its own built-in defaults.

### Custom Stub Format

All stubs use the `{{ tokenName }}` placeholder syntax. You can add your own tokens by extending `AbstractGenerator` and passing them through `buildTokens()`:

```php
protected function buildTokens(string $context, string $className, array $extra = []): array
{
    return parent::buildTokens($context, $className, array_merge([
        'myCustomToken' => 'myValue',
    ], $extra));
}
```

### Renaming Layers

You can rename any layer directory by editing the `layers` key in `config/ddd.php`:

```php
'layers' => [
    'domain'         => 'Core',          // was 'Domain'
    'application'    => 'UseCases',      // was 'Application'
    'infrastructure' => 'Adapters',      // was 'Infrastructure'
    'presentation'   => 'Delivery',      // was 'Presentation'
],
```

All namespace and path calculations update automatically.

---

## Auto-Discovery

When `auto_discover_contexts` is `true` (the default), the package scans the configured `base_path` on every application boot. Any directory that contains a `Domain/` sub-directory is recognized as a bounded context and registered automatically.

```
app/
├── ProductCatalog/
│   └── Domain/       ← presence of this marks it as a context
└── OrderManagement/
    └── Domain/       ← detected automatically
```

### Auto-Registering Context Providers

When `auto_register_providers` is also `true`, the package attempts to register a `{ContextName}ServiceProvider` class from each discovered context's root namespace:

```php
// Automatically registered if the class exists:
App\ProductCatalog\ProductCatalogServiceProvider
App\OrderManagement\OrderManagementServiceProvider
```

To disable either feature, set the config values to `false`:

```php
'auto_discover_contexts'  => false,
'auto_register_providers' => false,
```

### Manual Context Registration

You can register contexts programmatically via the `Ddd` facade or the `ContextRegistrar` contract:

```php
use LaravelDDD\Facades\Ddd;

Ddd::register('ProductCatalog', app_path('ProductCatalog'));
```

---

## SharedKernel

The SharedKernel is scaffolded at `app/SharedKernel/` the first time you run `ddd:make-context`. It contains:

### Contracts

| Contract | Location | Purpose |
|---|---|---|
| `EntityContract` | `SharedKernel/Contracts/EntityContract.php` | Identity and equality for domain entities |
| `AggregateRootContract` | `SharedKernel/Contracts/AggregateRootContract.php` | Aggregate root with domain event collection |
| `RepositoryContract` | `SharedKernel/Contracts/RepositoryContract.php` | Base repository interface |

### Value Objects

| Value Object | Location | Description |
|---|---|---|
| `Uuid` | `SharedKernel/ValueObjects/Uuid.php` | Immutable UUID value object |
| `Email` | `SharedKernel/ValueObjects/Email.php` | Validated email address value object |
| `Money` | `SharedKernel/ValueObjects/Money.php` | Monetary amount with currency |

These are shipped as part of the package source (`src/SharedKernel/`) and are available immediately after installation:

```php
use LaravelDDD\SharedKernel\ValueObjects\Uuid;
use LaravelDDD\SharedKernel\ValueObjects\Email;
use LaravelDDD\SharedKernel\ValueObjects\Money;
use LaravelDDD\SharedKernel\Contracts\EntityContract;
```

---

## Facade

The `Ddd` facade provides access to the `ContextRegistrar` service:

```php
use LaravelDDD\Facades\Ddd;

// Discover contexts under a path
Ddd::discover(app_path());

// List all registered contexts
$contexts = Ddd::all();

// Check if a context exists
if (Ddd::has('ProductCatalog')) { ... }

// Get a single context
$context = Ddd::get('ProductCatalog');
// Returns: ['name' => 'ProductCatalog', 'path' => '...', 'namespace' => '...']

// Register a context manually
Ddd::register('CustomContext', '/absolute/path/to/context');
```

---

## Extension Patterns

The package is designed to be extended without modifying the core. Here is how to add new generators and commands.

### Adding a New Generator

**1. Create a generator class** in `src/Generators/` (or your own namespace) extending `AbstractGenerator`:

```php
namespace App\DDD\Generators;

use LaravelDDD\Generators\AbstractGenerator;

class PolicyGenerator extends AbstractGenerator
{
    public function getStubName(): string
    {
        return 'policy'; // maps to stubs/policy.stub
    }

    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'];
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'domain', 'Policies');
        $tokens    = $this->buildTokens($contextName, $className, ['namespace' => $namespace]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'domain', 'Policies', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
```

**2. Create the stub file** at `stubs/policy.stub`:

```php
<?php

declare(strict_types=1);

namespace {{ namespace }};

/**
 * Domain Policy: {{ className }}
 *
 * TODO: Implement policy rules.
 */
final class {{ className }}
{
    // TODO: Add policy methods
}
```

**3. Bind the generator** in a service provider:

```php
$this->app->singleton(PolicyGenerator::class, fn ($app) => new PolicyGenerator(
    $app['config'],
    $app->make(\LaravelDDD\Support\StubRenderer::class),
    $app->make(\LaravelDDD\Support\FileGenerator::class),
));
```

### Adding a New Artisan Command

**1. Create a command class** extending `Illuminate\Console\Command`:

```php
namespace App\DDD\Commands;

use Illuminate\Console\Command;
use App\DDD\Generators\PolicyGenerator;

class MakePolicyCommand extends Command
{
    protected $signature = 'ddd:make-policy
                            {context : The bounded context name}
                            {name    : The policy class name}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a Domain Policy class';

    public function __construct(protected PolicyGenerator $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $created = $this->generator->generate([
            'context' => (string) $this->argument('context'),
            'name'    => (string) $this->argument('name'),
            'force'   => (bool) $this->option('force'),
        ]);

        if (! $created) {
            $this->warn('Policy already exists. Use --force to overwrite.');
            return self::FAILURE;
        }

        $this->info('✓ Policy created successfully.');
        return self::SUCCESS;
    }
}
```

**2. Register the command** in your `AppServiceProvider` or a dedicated service provider:

```php
if ($this->app->runningInConsole()) {
    $this->commands([
        \App\DDD\Commands\MakePolicyCommand::class,
    ]);
}
```

---

## End-to-End Example

Here is a complete walkthrough creating a `ProductCatalog` bounded context from scratch.

### 1. Scaffold the Context

```bash
php artisan ddd:make-context ProductCatalog
```

### 2. Create the Domain Model

```bash
# Entity
php artisan ddd:make-entity ProductCatalog Product

# Value Objects
php artisan ddd:make-value-object ProductCatalog ProductName
php artisan ddd:make-value-object ProductCatalog ProductPrice

# Domain Event
php artisan ddd:make-event ProductCatalog ProductCreated

# Domain Service
php artisan ddd:make-service ProductCatalog ProductPricingService

# Specification
php artisan ddd:make-specification ProductCatalog ProductIsAvailable

# Aggregate Root
php artisan ddd:make-aggregate ProductCatalog ProductAggregate
```

### 3. Create Repository Pair

```bash
php artisan ddd:make-repository ProductCatalog Product
```

Creates:
- `app/ProductCatalog/Domain/Repositories/ProductRepositoryInterface.php`
- `app/ProductCatalog/Infrastructure/Repositories/EloquentProductRepository.php`

### 4. Create CQRS Write Side

```bash
php artisan ddd:make-command ProductCatalog CreateProduct
php artisan ddd:make-dto ProductCatalog CreateProduct
```

Creates:
- `app/ProductCatalog/Application/Commands/CreateProductCommand.php`
- `app/ProductCatalog/Application/Handlers/CreateProductCommandHandler.php`
- `app/ProductCatalog/Application/DTOs/CreateProductDTO.php`

### 5. Create CQRS Read Side

```bash
php artisan ddd:make-query ProductCatalog GetProduct
php artisan ddd:make-query ProductCatalog ListProducts
```

Creates:
- `app/ProductCatalog/Application/Queries/GetProductQuery.php`
- `app/ProductCatalog/Application/Handlers/GetProductQueryHandler.php`
- `app/ProductCatalog/Application/Queries/ListProductsQuery.php`
- `app/ProductCatalog/Application/Handlers/ListProductsQueryHandler.php`

### 6. Bind the Repository in the Context Provider

Open `app/ProductCatalog/ProductCatalogServiceProvider.php` and register the binding:

```php
public function register(): void
{
    $this->app->bind(
        \App\ProductCatalog\Domain\Repositories\ProductRepositoryInterface::class,
        \App\ProductCatalog\Infrastructure\Repositories\EloquentProductRepository::class,
    );
}
```

### 7. Verify Everything

```bash
php artisan ddd:list-contexts
php artisan ddd:info
```

---

## Testing

### Running Tests

```bash
vendor/bin/phpunit
```

To run a specific test suite:

```bash
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Feature
```

### Test Structure

| Directory | Contents |
|---|---|
| `tests/Unit/` | Isolated unit tests for generators, the resolver, the stub renderer, etc. |
| `tests/Feature/` | End-to-end Artisan command tests that assert file creation |

### Base Test Class

All tests extend `LaravelDDD\Tests\TestCase`, which extends Orchestra Testbench's `TestCase` and automatically loads the `DddServiceProvider`.

### Writing Command Tests

```php
use LaravelDDD\Tests\TestCase;

class MakeEntityCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir().'/ddd_test_'.uniqid();
        mkdir($this->tempDir, 0755, true);
        config(['ddd.base_path' => $this->tempDir]);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        parent::tearDown();
    }

    public function test_creates_entity_file(): void
    {
        $this->artisan('ddd:make-entity', [
            'context' => 'ProductCatalog',
            'name'    => 'Product',
        ])->assertSuccessful();

        $this->assertFileExists(
            $this->tempDir.'/ProductCatalog/Domain/Entities/Product.php'
        );
    }
}
```

### CI Matrix

The package is tested via GitHub Actions against all supported combinations:

| PHP | Laravel |
|---|---|
| 8.1 | 10 |
| 8.2 | 10, 11, 12 |
| 8.3 | 10, 11, 12 |

---

## FAQ

**Q: Can I use a different directory layout than `app/`?**

A: Yes. Change `base_path` and `namespace_root` in `config/ddd.php`:

```php
'namespace_root' => 'Src',
'base_path'      => 'src',
```

All namespaces and file paths are computed dynamically from configuration.

---

**Q: Can I rename the `Domain/`, `Application/`, etc. directories?**

A: Yes. Edit the `layers` key in `config/ddd.php`. The package resolves every namespace and path from that mapping.

---

**Q: How do I stop the package from auto-registering context providers?**

A: Set `auto_register_providers` to `false` in `config/ddd.php`. You can then register providers manually in your `AppServiceProvider`.

---

**Q: A file already exists and I want to regenerate it — how?**

A: Pass the `--force` flag to any `ddd:make-*` command:

```bash
php artisan ddd:make-entity ProductCatalog Product --force
```

---

**Q: I published stubs and edited them, but the package is still using the defaults.**

A: Make sure you have set `stubs_path` in `config/ddd.php` to the directory containing your published stubs:

```php
'stubs_path' => base_path('stubs/ddd'),
```

---

**Q: How do I add a completely custom generator for something not covered by the package?**

A: See the [Extension Patterns](#extension-patterns) section. Create a class extending `AbstractGenerator`, create a matching stub, bind the generator in a service provider, and register an Artisan command.

---

**Q: Where does the `ddd:make-context` command create the `ServiceProvider`?**

A: It is placed at `app/{ContextName}/{ContextName}ServiceProvider.php` (respecting your configured `base_path`). This provider is automatically registered on boot when `auto_register_providers` is `true`.

---

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Copyright © 2026 [Kasun Vimarshana](https://github.com/kasunvimarshana)
