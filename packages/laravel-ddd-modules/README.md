# Laravel DDD Modules

A fully dynamic, customizable Laravel package for scaffolding **Domain-Driven Design (DDD)** modular structures via Artisan commands. Generate complete, production-ready module skeletons with a single command.

---

## Features

- 🏗️ **Full DDD structure** — Domain, Application, Infrastructure, Presentation layers
- ⚡ **Single command** module generation (`make:ddd-module`)
- 🔧 **Individual generators** for entities, value objects, use cases
- 📋 **Configurable** — customize paths, namespaces, and directory structure
- 🎨 **Custom stubs** — publish and override any stub template
- 🔍 **Auto-discovery** — automatically registers module service providers
- ✅ **No extra dependencies** — only Laravel/Illuminate

---

## Requirements

- PHP 8.1+
- Laravel 10.x or 11.x

---

## Installation

```bash
composer require kasunvimarshana/laravel-ddd-modules
```

The service provider is auto-discovered via Laravel's package discovery.

### Publish Config

```bash
php artisan vendor:publish --tag=ddd-modules-config
```

### Publish Stubs (optional)

```bash
php artisan vendor:publish --tag=ddd-modules-stubs
```

---

## Usage

### Generate a complete DDD module

```bash
php artisan make:ddd-module Order
php artisan make:ddd-module UserProfile
```

This creates:

```
app/Modules/Order/
├── Domain/
│   ├── Entities/          OrderEntity.php
│   ├── ValueObjects/      OrderId.php
│   ├── Aggregates/        OrderAggregate.php
│   ├── Repositories/      OrderRepositoryInterface.php
│   ├── Services/          OrderDomainService.php
│   ├── Events/            OrderCreated.php
│   ├── Policies/
│   ├── Enums/
│   ├── Specifications/
│   ├── Exceptions/
│   ├── Traits/
│   └── Contracts/
├── Application/
│   ├── UseCases/          CreateOrderUseCase.php
│   ├── DTOs/              CreateOrderDTO.php
│   ├── Commands/
│   ├── Queries/
│   ├── Handlers/
│   ├── Mappers/
│   ├── Validators/
│   ├── Services/
│   ├── Contracts/
│   └── Exceptions/
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/      OrderModel.php
│   │   ├── Repositories/  EloquentOrderRepository.php
│   │   ├── Migrations/
│   │   ├── Factories/
│   │   ├── Seeders/
│   │   └── Casts/
│   ├── Providers/         OrderServiceProvider.php
│   ├── Services/
│   ├── Events/
│   ├── Jobs/
│   ├── Notifications/
│   ├── Logging/
│   └── Integrations/
└── Presentation/
    ├── Http/
    │   ├── Controllers/
    │   │   ├── Api/       OrderController.php
    │   │   └── Web/       OrderController.php
    │   ├── Requests/      StoreOrderRequest.php
    │   ├── Resources/     OrderResource.php
    │   ├── Routes/        api.php, web.php
    │   ├── Middleware/
    │   └── Exceptions/
    ├── Console/Commands/
    └── Views/
```

### Options

```bash
# Overwrite existing module
php artisan make:ddd-module Order --force

# Only create directories (no stub files)
php artisan make:ddd-module Order --without-stubs

# Generate only specific layers
php artisan make:ddd-module Order --only=Domain,Application
```

### Generate individual components

```bash
# Generate an entity
php artisan make:ddd-entity Order LineItem

# Generate a value object
php artisan make:ddd-value-object Order Money

# Generate a use case
php artisan make:ddd-use-case Order UpdateOrder
```

### List all modules

```bash
php artisan ddd:list-modules
```

---

## Configuration

After publishing, edit `config/ddd-modules.php`:

```php
return [
    'modules_path'      => app_path('Modules'),
    'modules_namespace' => 'App\\Modules',
    'structure'         => [ /* customize layers and subdirectories */ ],
    'stubs'             => [
        'path'     => null,       // custom stubs directory
        'generate' => [ /* toggle individual file generation */ ],
    ],
    'auto_discover'     => true,  // auto-register module providers
];
```

### Autoloading

Add your modules namespace to `composer.json`:

```json
"autoload": {
    "psr-4": {
        "App\\Modules\\": "app/Modules/"
    }
}
```

Then run `composer dump-autoload`.

---

## Testing

```bash
composer test
```

---

## License

MIT. See [LICENSE](LICENSE).