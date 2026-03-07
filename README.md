# Laravel IAM - Microservice-Driven Inventory Management System

A comprehensive **microservice-driven Inventory Management System** built with **Laravel** (backend services) and **React** (frontend). Demonstrates enterprise-grade patterns including modular architecture, CQRS-inspired design, event-driven communication, Keycloak authentication/authorization (RBAC & ABAC), Saga pattern for distributed transactions, and webhook integration.

---

## 📐 Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         React Frontend                          │
│           (Vite + TypeScript + Keycloak-js + TanStack Query)    │
└───────────────────────────┬─────────────────────────────────────┘
                            │ HTTP (JWT Bearer Token)
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Nginx API Gateway                          │
│            (Reverse proxy + load balancing + TLS)               │
└──────┬──────────────┬──────────────┬──────────────┬────────────┘
       │              │              │              │
       ▼              ▼              ▼              ▼
┌───────────┐  ┌────────────┐  ┌──────────┐  ┌──────────┐
│  Product  │  │ Inventory  │  │  Order   │  │   User   │
│  Service  │  │  Service   │  │ Service  │  │ Service  │
│  (8001)   │  │  (8002)    │  │  (8003)  │  │  (8004)  │
└─────┬─────┘  └──────┬─────┘  └────┬─────┘  └────┬─────┘
      │               │             │              │
      └───────────────┴──────┬──────┴──────────────┘
                             │
          ┌──────────────────┼──────────────────┐
          ▼                  ▼                  ▼
   ┌─────────────┐   ┌──────────────┐   ┌─────────────┐
   │   MySQL     │   │   RabbitMQ   │   │    Redis    │
   │ (Per-service│   │ (Event Bus)  │   │   (Cache)   │
   │  database)  │   │              │   │             │
   └─────────────┘   └──────────────┘   └─────────────┘
          │
   ┌──────────────┐
   │   Keycloak   │
   │    (8080)    │
   │ (Auth/AuthZ) │
   └──────────────┘
```

---

## 🗂️ Repository Structure

```
LaravelIAM/
├── services/
│   ├── product-service/         # Product microservice (Laravel)
│   │   └── app/Modules/Product/
│   │       ├── Controllers/     # HTTP layer only
│   │       ├── Services/        # Business logic
│   │       ├── Repositories/    # Data access (with interfaces)
│   │       ├── Models/          # Eloquent models
│   │       ├── Requests/        # Form Request validation
│   │       ├── Resources/       # API Resources (standardized responses)
│   │       ├── Events/          # Domain events
│   │       ├── Listeners/       # Event handlers
│   │       ├── DTOs/            # Data Transfer Objects
│   │       └── Routes/          # Module-specific routes
│   ├── inventory-service/       # Inventory microservice (Laravel)
│   │   └── app/Modules/Inventory/  # Same modular structure
│   ├── order-service/           # Order microservice (Laravel + Saga)
│   │   └── app/Modules/
│   │       ├── Order/           # Order module
│   │       └── Saga/            # Saga orchestration
│   └── user-service/            # User microservice (Laravel + Keycloak)
│       └── app/Modules/User/    # Same modular structure
├── frontend/                    # React frontend
│   └── src/
│       ├── context/             # AuthContext (Keycloak)
│       ├── components/          # UI components per domain
│       ├── services/            # API service layers
│       └── pages/               # Page components
├── docker/
│   ├── mysql/init.sql           # Multi-database initialization
│   └── nginx/nginx.conf         # API Gateway configuration
├── docker-compose.yml           # Complete orchestration
└── README.md                    # This file
```

---

## 🧱 Modular Architecture: Controller → Service → Repository

Each module strictly follows the three-layer pattern:

| Layer | Responsibility | Example |
|-------|---------------|---------|
| **Controller** | Handle HTTP request/response only. Delegates to Service. | `ProductController` |
| **Service** | Business logic, orchestration, transactions, event dispatching | `ProductService` |
| **Repository** | Database interactions via Eloquent. Implements interface. | `ProductRepository` |

### Dependency Injection via Repository Interface

```php
// Service Provider registers the binding
$this->app->bind(
    ProductRepositoryInterface::class,
    ProductRepository::class
);

// Service receives interface — decoupled from implementation
class ProductService {
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}
}
```

---

## 🔐 Authentication & Authorization

### Keycloak Setup

1. Start Keycloak: `docker compose up keycloak`
2. Navigate to `http://localhost:8080`
3. Create realm: `inventory-realm`
4. Create clients: `inventory-frontend`, `product-service`, `inventory-service`, `order-service`, `user-service`
5. Create roles: `admin`, `manager`, `warehouse-manager`, `viewer`, `customer`
6. Assign roles to users

### RBAC (Role-Based Access Control)

```php
// In routes
Route::middleware('keycloak.role:admin')->group(function () {
    Route::apiResource('users', UserController::class);
});

// In Keycloak middleware — extracts roles from JWT
$realmRoles  = $tokenData['realm_access']['roles'] ?? [];
$clientRoles = $tokenData['resource_access'][$clientId]['roles'] ?? [];
```

### ABAC (Attribute-Based Access Control)

```php
// User model with attribute support
class User extends Authenticatable {
    public function hasAttribute(string $key, mixed $value): bool {
        return isset($this->attributes[$key]) && $this->attributes[$key] === $value;
    }
}

// UserService ABAC check
$allowed = $userService->userHasAttribute($userId, 'department', 'warehouse');
```

### Service-to-Service Authentication

Services communicate using Keycloak-issued JWT tokens via the client credentials flow:

```php
// Service fetches token from Keycloak
$response = Http::asForm()->post('keycloak/token', [
    'grant_type'    => 'client_credentials',
    'client_id'     => config('keycloak.client_id'),
    'client_secret' => config('keycloak.client_secret'),
]);

// ServiceAuthMiddleware validates the token
Route::prefix('internal/v1')->middleware('service.auth')->group(...)
```

---

## 📦 Modules

### Product Service (`:8001`)

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/v1/products` | GET | ✅ | List with pagination, filtering, sorting |
| `/api/v1/products/{id}` | GET | ✅ | Get product by ID |
| `/api/v1/products` | POST | ✅ admin/manager | Create product |
| `/api/v1/products/{id}` | PUT | ✅ admin/manager | Update product |
| `/api/v1/products/{id}` | DELETE | ✅ admin | Delete product |
| `/api/v1/webhooks` | POST | ✅ | Register webhook |
| `/health` | GET | ❌ | Health check |

**Filtering parameters**: `search`, `category`, `status`, `min_price`, `max_price`, `sort_by`, `sort_direction`, `per_page`, `page`

### Inventory Service (`:8002`)

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/v1/inventory` | GET | ✅ | List inventory |
| `/api/v1/inventory/{id}` | GET | ✅ | Get by ID |
| `/api/v1/inventory/product/{id}` | GET | ✅ | Get by product ID |
| `/api/v1/inventory` | POST | ✅ | Create inventory record |
| `/api/v1/inventory/{id}` | PUT | ✅ | Update inventory |
| `/api/v1/inventory/product/{id}/adjust` | POST | ✅ | Adjust quantity |
| `/internal/v1/inventory/product/{id}/reserve` | POST | 🔒 service | Reserve stock |
| `/internal/v1/inventory/product/{id}/release` | POST | 🔒 service | Release reservation |
| `/health` | GET | ❌ | Health check |

**Filtering**: `product_id`, `warehouse_location`, `low_stock`, `in_stock`

### Order Service (`:8003`)

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/v1/orders` | GET | ✅ | List orders |
| `/api/v1/orders/{id}` | GET | ✅ | Get order |
| `/api/v1/orders` | POST | ✅ | Create order (triggers Saga) |
| `/api/v1/orders/{id}/status` | PATCH | ✅ admin/manager | Update status |
| `/api/v1/orders/{id}/cancel` | POST | ✅ | Cancel order |
| `/health` | GET | ❌ | Health check |

### User Service (`:8004`)

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/v1/users` | GET | ✅ admin | List users |
| `/api/v1/users/{id}` | GET | ✅ | Get user |
| `/api/v1/users` | POST | ✅ admin | Create user (+ Keycloak) |
| `/api/v1/users/{id}` | PUT | ✅ admin | Update user |
| `/api/v1/users/{id}` | DELETE | ✅ admin | Delete user |
| `/api/v1/users/{id}/check-role` | POST | ✅ | RBAC check |
| `/api/v1/users/{id}/check-attribute` | POST | ✅ | ABAC check |
| `/health` | GET | ❌ | Health check |

---

## 🔄 Event-Driven Architecture (RabbitMQ)

Events flow through a `topic` exchange (`inventory_exchange`):

```
Product Service                    Inventory Service
┌──────────────┐   product.created  ┌───────────────────┐
│ProductCreated│ ─────────────────> │HandleProductCreated│
│ProductDeleted│ ─────────────────> │HandleProductDeleted│
└──────────────┘   product.deleted  └───────────────────┘
```

### Domain Events

| Event | Publisher | Consumers | Trigger |
|-------|-----------|-----------|---------|
| `ProductCreated` | Product Service | Inventory Service | Product created |
| `ProductUpdated` | Product Service | — | Product updated |
| `ProductDeleted` | Product Service | Inventory Service | Product deleted (cleanup) |
| `InventoryUpdated` | Inventory Service | — | Stock changed |
| `LowStockAlert` | Inventory Service | — | Quantity ≤ reorder level |
| `OrderCreated` | Order Service | — | Order confirmed |
| `OrderCancelled` | Order Service | Inventory Service | Reservation released |

---

## 🔁 Saga Pattern (Distributed Transactions)

The `OrderCreationSaga` coordinates a multi-step distributed transaction:

```
Step 1: Create Order (local)          ✅
Step 2: Reserve Inventory (remote)    ✅
Step 3: Confirm Order                 ✅

If Step 2 fails:
  Compensation: Cancel Order          🔄

If Step 3 fails:
  Compensation: Release Inventory     🔄
  Compensation: Cancel Order          🔄
```

Saga state is persisted in the `saga_state` JSON column on the `orders` table for idempotency.

---

## 🪝 Webhook Integration

### Register a Webhook

```bash
POST /api/v1/webhooks
{
  "url": "https://your-system.com/webhooks/inventory",
  "events": ["product.created", "product.deleted"],
  "secret": "your-webhook-secret-key"
}
```

### Webhook Payload (DTO)

```json
{
  "event": "product.created",
  "payload": { "product": { "id": 1, "name": "Widget", "sku": "WGT-001" } },
  "timestamp": "2024-01-15T10:00:00.000Z",
  "webhook_id": "wh_65a4b7c8d9e0f",
  "signature": "sha256-hmac-signature"
}
```

---

## 🚀 Getting Started

### Prerequisites

- Docker & Docker Compose v2+

### 1. Clone and Configure

```bash
git clone https://github.com/kasunvimarshana/LaravelIAM.git
cd LaravelIAM
```

### 2. Start All Services

```bash
docker compose up -d
```

### 3. Run Migrations

```bash
docker compose exec product-service php artisan migrate
docker compose exec inventory-service php artisan migrate
docker compose exec order-service php artisan migrate
docker compose exec user-service php artisan migrate
```

### 4. Configure Keycloak

1. Open `http://localhost:8080/admin` (admin/admin)
2. Create realm: `inventory-realm`
3. Create clients for each service
4. Create roles and assign to users

### 5. Access the Application

| Service | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| API Gateway | http://localhost:80 |
| Product API | http://localhost:8001 |
| Inventory API | http://localhost:8002 |
| Order API | http://localhost:8003 |
| User API | http://localhost:8004 |
| Keycloak | http://localhost:8080 |
| RabbitMQ Management | http://localhost:15672 |

---

## 🧪 Running Tests

```bash
docker compose exec product-service php artisan test
docker compose exec inventory-service php artisan test
docker compose exec order-service php artisan test
docker compose exec user-service php artisan test
```

---

## 🏗️ Design Patterns

| Pattern | Location | Purpose |
|---------|----------|---------|
| Repository Pattern | All services | Decouple data access from business logic |
| Service Layer | All services | Business logic encapsulation |
| DTO | All modules | Type-safe data passing |
| Observer/Event | All services | Loose coupling |
| Saga | Order Service | Distributed transaction management |
| API Resource | All modules | Standardized HTTP responses |

---

## 📚 Key Technologies

| Technology | Purpose |
|-----------|---------|
| Laravel 11 | Backend microservices framework |
| React 18 + TypeScript | Frontend SPA |
| Keycloak 23 | Identity & Access Management |
| RabbitMQ | Event/message broker |
| MySQL 8 | Relational database |
| Redis 7 | Caching |
| Nginx | API Gateway |
| Docker Compose | Local orchestration |
| TanStack Query | Frontend data fetching |
| PHPUnit | Backend testing |
