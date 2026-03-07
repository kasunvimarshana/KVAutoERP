"# Multi-Tenant SaaS Inventory Management System

A production-ready **multi-tenant SaaS Inventory Management System** built with **Laravel 11** (backend) and **React 18** (frontend), featuring **Keycloak-based SSO authentication**, **RBAC/ABAC authorization**, modular microservice architecture, event-driven communication via **RabbitMQ**, and scalable multi-tenant API design.

---

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Backend: Laravel API](#backend-laravel-api)
  - [Modular Architecture](#modular-architecture)
  - [Authentication & Authorization](#authentication--authorization)
  - [Multi-Tenancy](#multi-tenancy)
  - [Modules](#modules)
  - [Event-Driven Architecture](#event-driven-architecture)
  - [Saga Pattern](#saga-pattern)
  - [Webhook Integration](#webhook-integration)
  - [Health Check Endpoints](#health-check-endpoints)
  - [API Features](#api-features)
- [Frontend: React SPA](#frontend-react-spa)
- [Configuration](#configuration)
- [Testing](#testing)
- [Docker Deployment](#docker-deployment)
- [API Reference](#api-reference)
- [Security](#security)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     Multi-Tenant SaaS Platform                   │
├──────────────┬──────────────┬──────────────┬────────────────────┤
│  User Module │Product Module│Inventory Mod.│   Order Module     │
│  Controller  │  Controller  │  Controller  │    Controller      │
│  Service     │  Service     │  Service     │    Service         │
│  Repository  │  Repository  │  Repository  │    Repository      │
│  Model       │  Model       │  Model       │    OrderSagaService │
├──────────────┴──────────────┴──────────────┴────────────────────┤
│        Tenant Middleware → Keycloak JWT Middleware               │
│        RBAC / ABAC Policy Enforcement (CheckPermission)         │
├─────────────────────────────────────────────────────────────────┤
│   RabbitMQ Event Bus (ProductCreated, InventoryUpdated, etc.)   │
├───────────────┬─────────────────┬───────────────────────────────┤
│  MySQL 8.0    │    Redis 7       │    Keycloak 23 (SSO/IdP)     │
└───────────────┴─────────────────┴───────────────────────────────┘
```

### Key Design Principles

1. **Controller → Service → Repository Pattern**: Controllers handle HTTP I/O only; Services contain business logic; Repositories abstract all database access.
2. **Multi-Tenancy via Middleware**: Every request is scoped to a tenant using `TenantMiddleware`, which reads `tenant_id` from the JWT claims or `X-Tenant-ID` header.
3. **Keycloak-First Authentication**: All JWTs are issued and validated against Keycloak. The `AuthenticateWithKeycloak` middleware decodes tokens using JWKS public keys cached in Redis.
4. **RBAC + ABAC**: Roles (`admin`, `manager`, `viewer`) are embedded in the Keycloak JWT. Attribute-based decisions are enforced via `CheckPermission` middleware by inspecting JWT claims.
5. **Event-Driven Microservices**: Domain events (`ProductCreated`, `InventoryUpdated`, `OrderCreated`, etc.) are published to RabbitMQ for cross-service communication.
6. **Saga Pattern**: `OrderSagaService` orchestrates distributed transactions across Inventory and Order services with compensating transactions on failure.

---

## Technology Stack

### Backend
| Component | Technology |
|-----------|-----------|
| Framework | Laravel 11 (PHP 8.2+) |
| Authentication | Keycloak 23 + JWT (firebase/php-jwt v7) |
| Database | MySQL 8.0 (Eloquent ORM) |
| Cache / Queue | Redis 7 |
| Message Broker | RabbitMQ 3.12 (php-amqplib) |
| API Querying | spatie/laravel-query-builder |
| HTTP Client | Guzzle 7 |

### Frontend
| Component | Technology |
|-----------|-----------|
| Framework | React 18 + TypeScript |
| Build Tool | Vite 5 |
| Routing | React Router v6 |
| SSO | keycloak-js v23 |
| HTTP Client | Axios |
| Styling | Tailwind CSS |
| Testing | Vitest + Testing Library |

### Infrastructure
| Component | Technology |
|-----------|-----------|
| Identity Provider | Keycloak 23 |
| Container Runtime | Docker + Docker Compose |
| Web Server | Nginx (Alpine) |
| Database | MySQL 8.0 |
| Cache | Redis 7 |
| Message Queue | RabbitMQ 3.12 |

---

## Project Structure

```
MultiTenent_SAAS_SSO_Laravel/
├── backend/                              # Laravel 11 API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Middleware/
│   │   │       ├── AuthenticateWithKeycloak.php  # JWT validation
│   │   │       ├── TenantMiddleware.php           # Tenant scoping
│   │   │       ├── CheckPermission.php            # RBAC/ABAC enforcement
│   │   │       └── VerifyServiceToken.php         # Inter-service auth
│   │   ├── Modules/
│   │   │   ├── User/                     # User module
│   │   │   │   ├── Controllers/UserController.php
│   │   │   │   ├── Services/UserService.php
│   │   │   │   ├── Repositories/
│   │   │   │   │   ├── UserRepositoryInterface.php
│   │   │   │   │   └── UserRepository.php
│   │   │   │   ├── Models/User.php
│   │   │   │   ├── Requests/
│   │   │   │   ├── Resources/UserResource.php
│   │   │   │   ├── DTOs/UserDTO.php
│   │   │   │   ├── Events/
│   │   │   │   ├── Listeners/SyncUserWithKeycloak.php
│   │   │   │   ├── Webhooks/UserWebhookHandler.php
│   │   │   │   └── Routes/api.php
│   │   │   ├── Product/                  # Product module
│   │   │   ├── Inventory/                # Inventory module
│   │   │   └── Order/                    # Order module
│   │   │       └── Services/OrderSagaService.php   # Saga orchestration
│   │   ├── Services/
│   │   │   ├── KeycloakService.php       # Keycloak admin API + JWT validation
│   │   │   └── MessageBrokerService.php  # RabbitMQ publisher
│   │   ├── Models/Tenant.php
│   │   └── Providers/
│   │       ├── ModuleServiceProvider.php
│   │       ├── RepositoryServiceProvider.php
│   │       └── EventServiceProvider.php
│   ├── database/migrations/
│   ├── routes/api.php
│   ├── config/
│   │   ├── keycloak.php
│   │   └── rabbitmq.php
│   ├── tests/
│   │   ├── Unit/   (UserServiceTest, ProductServiceTest, etc.)
│   │   └── Feature/ (UserApiTest, ProductApiTest, etc.)
│   ├── docker-compose.yml
│   └── Dockerfile
└── frontend/                             # React 18 SPA
    ├── src/
    │   ├── keycloak.ts                   # Keycloak PKCE configuration
    │   ├── services/                     # API service layer
    │   ├── context/                      # Auth + Tenant React contexts
    │   ├── hooks/                        # useAuth, useTenant hooks
    │   ├── components/
    │   │   ├── Layout.tsx                # Sidebar + RBAC nav
    │   │   ├── DataTable.tsx             # Pagination/search/sort
    │   │   └── ProtectedRoute.tsx
    │   ├── pages/
    │   │   ├── Dashboard.tsx
    │   │   ├── Users.tsx
    │   │   ├── Products.tsx
    │   │   ├── Inventory.tsx
    │   │   └── Orders.tsx
    │   └── types/index.ts
    └── package.json
```

---

## Getting Started

### Prerequisites

- Docker & Docker Compose
- PHP 8.2+ and Composer (for local development)
- Node.js 20+ and npm (for frontend development)

### Quick Start with Docker

```bash
# Clone the repository
git clone https://github.com/kasunvimarshana/MultiTenent_SAAS_SSO_Laravel.git
cd MultiTenent_SAAS_SSO_Laravel

# Start all services
cd backend
cp .env.example .env
docker-compose up -d

# Wait for services to start (~60 seconds for Keycloak)
# Run migrations
docker-compose exec app php artisan migrate --seed

# Access services:
# API:       http://localhost:8000/api
# Keycloak:  http://localhost:8080  (admin/admin)
# RabbitMQ:  http://localhost:15672 (guest/guest)
```

### Local Development Setup

```bash
# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve  # Runs on http://localhost:8000

# Frontend
cd ../frontend
npm install
cp .env.example .env.local   # Set VITE_KEYCLOAK_URL, VITE_API_URL
npm run dev                   # Runs on http://localhost:5173
```

---

## Backend: Laravel API

### Modular Architecture

Each domain module lives under `app/Modules/{ModuleName}/` and contains all layers needed for that domain:

```
app/Modules/Product/
├── Controllers/ProductController.php    # HTTP layer only
├── Services/ProductService.php          # Business logic
├── Repositories/
│   ├── ProductRepositoryInterface.php   # Abstraction contract
│   └── ProductRepository.php           # Eloquent implementation
├── Models/Product.php                   # Eloquent model
├── Requests/
│   ├── CreateProductRequest.php         # Form Request validation
│   └── UpdateProductRequest.php
├── Resources/ProductResource.php        # API Response transformation
├── DTOs/ProductDTO.php                  # Immutable data transfer object
├── Events/
│   ├── ProductCreated.php
│   ├── ProductUpdated.php
│   └── ProductDeleted.php
├── Listeners/
│   ├── NotifyInventoryOnProductCreated.php
│   └── NotifyInventoryOnProductDeleted.php
├── Webhooks/ProductWebhookHandler.php   # Incoming webhook processing
├── DTOs/ProductWebhookDTO.php           # Webhook payload structure
└── Routes/api.php                       # Module-scoped routes
```

**Dependency Injection**: `RepositoryServiceProvider` binds all `*RepositoryInterface` contracts to their concrete `*Repository` implementations, enabling easy swapping and testing.

### Authentication & Authorization

#### Keycloak JWT Authentication

All API endpoints (except `/health`) require a valid Keycloak-issued JWT Bearer token:

```
Authorization: Bearer <keycloak_access_token>
```

The `AuthenticateWithKeycloak` middleware:
1. Extracts the Bearer token from the request
2. Fetches JWKS public keys from Keycloak (cached 1 hour in Redis)
3. Decodes and validates the JWT signature, expiry, and issuer
4. Injects decoded claims into the request (`auth_user`, `tenant_id`)

#### RBAC (Role-Based Access Control)

Roles are embedded in the Keycloak JWT under `realm_access.roles`. The `CheckPermission` middleware enforces:

| Role | Permissions |
|------|-------------|
| `admin` | Full CRUD on all resources |
| `manager` | Read + Write on Products, Inventory, Orders |
| `viewer` | Read-only access |

```php
// Route protection example
Route::middleware(['keycloak.auth', 'permission:admin,manager'])
    ->post('/products', [ProductController::class, 'store']);
```

#### ABAC (Attribute-Based Access Control)

Attribute-based decisions are made using JWT claims such as `tenant_id`, `department`, and `resource_access`. The `CheckPermission` middleware validates that the requesting user's attributes satisfy resource-level policies.

### Multi-Tenancy

The `TenantMiddleware` enforces tenant isolation on every request:

1. Reads `tenant_id` from the decoded JWT claims
2. Falls back to `X-Tenant-ID` request header
3. Stores the `tenant_id` in the service container (`app('tenant_id')`)
4. All repository queries automatically scope to the current tenant

```php
// Repository automatically scopes to tenant
public function findAll(string $tenantId, int $perPage): LengthAwarePaginator
{
    return $this->model
        ->where('tenant_id', $tenantId)
        ->paginate($perPage);
}
```

### Modules

#### User Module
- Full CRUD with Keycloak synchronization
- `SyncUserWithKeycloak` listener creates/updates users in Keycloak on every user mutation
- Soft deletes with restore capability
- Filtering by role, active status, and full-text search

#### Product Module
- Full CRUD with SKU uniqueness per tenant
- `ProductCreated` event triggers `NotifyInventoryOnProductCreated` listener to auto-create inventory records
- `ProductDeleted` event triggers inventory cleanup

#### Inventory Module
- Quantity management with low-stock threshold alerts
- Location and warehouse tracking
- `InventoryUpdated` event published to RabbitMQ for cross-service notification
- Bulk quantity adjustment endpoint

#### Order Module
- Full order lifecycle (pending → processing → completed / cancelled)
- Order items with quantity and price tracking
- Inventory reservation via Saga pattern
- `OrderCreated`, `OrderCompleted`, `OrderCancelled` domain events

### Event-Driven Architecture

Domain events are published to **RabbitMQ** via `MessageBrokerService`:

```php
// Dispatching an event
event(new ProductCreated($product));

// Listener publishes to RabbitMQ
class NotifyInventoryOnProductCreated
{
    public function handle(ProductCreated $event): void
    {
        $this->messageBroker->publish('product.created', [
            'product_id' => $event->product->id,
            'tenant_id'  => $event->product->tenant_id,
            'sku'        => $event->product->sku,
        ]);
    }
}
```

**Published events:**

| Event | Exchange | Consumer |
|-------|----------|----------|
| `ProductCreated` | `product.created` | Inventory service |
| `ProductDeleted` | `product.deleted` | Inventory service |
| `InventoryUpdated` | `inventory.updated` | Order service |
| `OrderCreated` | `order.created` | Inventory reservation |
| `OrderCancelled` | `order.cancelled` | Inventory release |
| `UserCreated` | `user.created` | Notification service |

### Saga Pattern

`OrderSagaService` implements the **Orchestration Saga pattern** for distributed order processing:

```
OrderSaga Steps:
  1. Reserve inventory (inventory service)
     ↓ (on failure: compensate → skip remaining steps)
  2. Create order record
     ↓ (on failure: compensate → release inventory reservation)
  3. Process payment (external service)
     ↓ (on failure: compensate → cancel order + release inventory)
  4. Confirm order → emit OrderCompleted event
```

```php
// Usage
$orderSagaService->createOrderWithSaga($orderDTO, $tenantId);
```

### Webhook Integration

Each module has a `WebhookHandler` that accepts incoming webhook payloads. Structured **Webhook DTOs** ensure type-safe payload handling:

```php
// Example webhook DTO
class ProductWebhookDTO
{
    public function __construct(
        public readonly string $event,
        public readonly string $productId,
        public readonly string $tenantId,
        public readonly array  $payload,
        public readonly string $timestamp,
    ) {}

    public static function fromArray(array $data): self { ... }
}
```

**Webhook endpoints:**

```
POST /api/webhooks/users
POST /api/webhooks/products
POST /api/webhooks/inventory
POST /api/webhooks/orders
```

### Health Check Endpoints

Each service exposes a health check endpoint:

```
GET /api/health              → Overall system health
GET /api/users/health        → User service health
GET /api/products/health     → Product service health
GET /api/inventory/health    → Inventory service health
GET /api/orders/health       → Order service health
```

Response:
```json
{
  "status": "ok",
  "service": "user-service",
  "timestamp": "2024-01-15T10:30:00Z",
  "checks": {
    "database": "ok",
    "cache": "ok"
  }
}
```

### API Features

All list endpoints support:

| Feature | Query Parameter | Example |
|---------|----------------|---------|
| Pagination | `per_page`, `page` | `?per_page=20&page=2` |
| Search | `search` | `?search=laptop` |
| Filtering | field name | `?role=admin&is_active=true` |
| Sorting | `sort` | `?sort=-created_at` (prefix `-` for DESC) |

---

## Frontend: React SPA

The React frontend integrates with the Laravel API using:

- **Keycloak PKCE flow** for authentication (no client secret required)
- **AuthContext** stores the authenticated user and Keycloak instance
- **TenantContext** stores the active tenant ID and injects `X-Tenant-ID` into all API calls
- **DataTable** component with built-in pagination, search, and column sorting
- **RBAC-aware UI**: navigation items and action buttons are conditionally rendered based on Keycloak roles

```
frontend/src/
├── keycloak.ts           # Keycloak PKCE configuration
├── context/
│   ├── AuthContext.tsx   # Keycloak-backed auth context
│   └── TenantContext.tsx # Tenant ID context
├── hooks/
│   ├── useAuth.ts        # Access auth state + Keycloak actions
│   └── useTenant.ts      # Access/set tenant ID
├── services/
│   ├── api.ts            # Axios + Bearer token + X-Tenant-ID
│   ├── userService.ts
│   ├── productService.ts
│   ├── inventoryService.ts
│   └── orderService.ts
├── components/
│   ├── Layout.tsx        # Sidebar with RBAC-filtered nav
│   ├── DataTable.tsx     # Reusable table with pagination
│   └── ProtectedRoute.tsx
└── pages/
    ├── Dashboard.tsx     # Summary stats
    ├── Users.tsx         # User management (CRUD)
    ├── Products.tsx      # Product catalog (CRUD)
    ├── Inventory.tsx     # Stock management (CRUD)
    └── Orders.tsx        # Order management (CRUD)
```

---

## Configuration

### Backend `.env` Key Variables

```dotenv
# Application
APP_NAME="SaaS Inventory"
APP_ENV=production
APP_KEY=               # Generated by: php artisan key:generate

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=saas_inventory
DB_USERNAME=saas_user
DB_PASSWORD=saas_password

# Keycloak
KEYCLOAK_SERVER_URL=http://keycloak:8080
KEYCLOAK_REALM_URL=http://keycloak:8080/realms/saas-inventory
KEYCLOAK_ADMIN_URL=http://keycloak:8080/admin/realms/saas-inventory
KEYCLOAK_CLIENT_ID=saas-inventory-api
KEYCLOAK_ADMIN_USERNAME=admin
KEYCLOAK_ADMIN_PASSWORD=admin

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### Frontend `.env.local`

```dotenv
VITE_API_URL=http://localhost:8000/api
VITE_KEYCLOAK_URL=http://localhost:8080
VITE_KEYCLOAK_REALM=saas-inventory
VITE_KEYCLOAK_CLIENT_ID=saas-inventory-spa
```

---

## Testing

### Backend Tests

```bash
cd backend

# Run all tests
php vendor/bin/phpunit

# Run specific test suite
php vendor/bin/phpunit --testsuite Unit
php vendor/bin/phpunit --testsuite Feature

# With coverage
php vendor/bin/phpunit --coverage-html coverage/
```

**Test coverage:**
- `UserServiceTest` - Unit tests for user business logic
- `ProductServiceTest` - Unit tests for product business logic
- `InventoryServiceTest` - Unit tests for inventory management
- `OrderServiceTest` - Unit tests for order + saga logic
- `UserApiTest` - Feature tests for user API endpoints
- `ProductApiTest` - Feature tests for product API endpoints
- `InventoryApiTest` - Feature tests for inventory API endpoints
- `OrderApiTest` - Feature tests for order API endpoints

### Frontend Tests

```bash
cd frontend
npm test
```

---

## Docker Deployment

```bash
cd backend

# Build and start all services
docker-compose up -d --build

# Check service health
docker-compose ps

# View logs
docker-compose logs -f app

# Run migrations inside container
docker-compose exec app php artisan migrate --seed

# Scale the application
docker-compose up -d --scale app=3
```

**Services exposed:**

| Service | Port | URL |
|---------|------|-----|
| Laravel API | 8000 | http://localhost:8000 |
| Keycloak | 8080 | http://localhost:8080 |
| RabbitMQ Management | 15672 | http://localhost:15672 |
| MySQL | 3306 | localhost:3306 |
| Redis | 6379 | localhost:6379 |

---

## API Reference

### Authentication

All requests require: `Authorization: Bearer <token>`
Multi-tenant requests require: `X-Tenant-ID: <tenant_uuid>`

### Users API

| Method | Endpoint | Description | Roles |
|--------|----------|-------------|-------|
| GET | `/api/users` | List users (paginated) | admin, manager |
| POST | `/api/users` | Create user | admin |
| GET | `/api/users/{id}` | Get user | admin, manager |
| PUT | `/api/users/{id}` | Update user | admin, manager |
| DELETE | `/api/users/{id}` | Delete user | admin |
| POST | `/api/users/{id}/restore` | Restore soft-deleted user | admin |
| GET | `/api/users/health` | Health check | public |

### Products API

| Method | Endpoint | Description | Roles |
|--------|----------|-------------|-------|
| GET | `/api/products` | List products | all |
| POST | `/api/products` | Create product | admin, manager |
| GET | `/api/products/{id}` | Get product | all |
| PUT | `/api/products/{id}` | Update product | admin, manager |
| DELETE | `/api/products/{id}` | Delete product | admin |
| GET | `/api/products/{id}/inventory` | Get product with inventory | all |
| GET | `/api/products/health` | Health check | public |

### Inventory API

| Method | Endpoint | Description | Roles |
|--------|----------|-------------|-------|
| GET | `/api/inventory` | List inventory | all |
| POST | `/api/inventory` | Create inventory record | admin, manager |
| GET | `/api/inventory/{id}` | Get inventory | all |
| PUT | `/api/inventory/{id}` | Update inventory | admin, manager |
| DELETE | `/api/inventory/{id}` | Delete inventory | admin |
| POST | `/api/inventory/{id}/adjust` | Adjust quantity | admin, manager |
| GET | `/api/inventory/health` | Health check | public |

### Orders API

| Method | Endpoint | Description | Roles |
|--------|----------|-------------|-------|
| GET | `/api/orders` | List orders | all |
| POST | `/api/orders` | Create order (Saga) | all |
| GET | `/api/orders/{id}` | Get order | all |
| PUT | `/api/orders/{id}` | Update order | admin, manager |
| DELETE | `/api/orders/{id}` | Cancel order | admin, manager |
| POST | `/api/orders/{id}/complete` | Complete order | admin, manager |
| GET | `/api/orders/health` | Health check | public |

---

## Security

### Authentication Security
- All JWT tokens validated against Keycloak JWKS endpoint
- JWKS public keys cached in Redis to prevent JWKS endpoint DoS
- Token expiry and issuer (`iss`) claims strictly validated
- RS256 algorithm enforced per JWKS key

### Multi-Tenant Isolation
- Every database query is scoped to `tenant_id` at the repository layer
- `TenantMiddleware` rejects requests without a valid tenant context
- Users can only access resources belonging to their own tenant

### Inter-Service Security
- `VerifyServiceToken` middleware validates service-to-service JWT tokens
- Service tokens issued by Keycloak with `service_account` grant type

### Data Protection
- Passwords never stored in the application (Keycloak manages credentials)
- Sensitive configuration via environment variables only
- HTTPS enforced in production via Nginx TLS termination

---

## Creating and Exploring the Project

### Exploring the Modular Structure

```bash
# List all module files
find backend/app/Modules -type f -name "*.php" | sort

# View a specific module's service
cat backend/app/Modules/Product/Services/ProductService.php

# Run tinker to explore the API
cd backend && php artisan tinker
```

### Adding a New Module

1. Create the module directory: `app/Modules/NewModule/`
2. Add Controller, Service, Repository interface + implementation, Model, Requests, Resource, DTO, Events, Listeners, Routes
3. Register the repository binding in `RepositoryServiceProvider`
4. Register route file in `ModuleServiceProvider`
5. Register events in `EventServiceProvider`
6. Create migration and run `php artisan migrate`

### Keycloak Setup Guide

1. Access Keycloak at `http://localhost:8080`
2. Create a realm named `saas-inventory`
3. Create two clients:
   - `saas-inventory-api` (bearer-only, for API validation)
   - `saas-inventory-spa` (public, standard flow + PKCE)
4. Create roles: `admin`, `manager`, `viewer`
5. Create realm attribute mapper for `tenant_id`
6. Create users and assign roles

---

*Built as a reference architecture for secure, scalable, event-driven, multi-tenant SaaS microservice systems using Laravel, React, and Keycloak with RBAC/ABAC authorization.*
" 
