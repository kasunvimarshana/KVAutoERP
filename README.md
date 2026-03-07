# SAAS_MultiTenent_SSO
Multi-tenant SaaS Inventory Management System with Laravel microservices and React frontend, featuring Laravel Passport SSO, RBAC/ABAC authorization, modular architecture, and event-driven communication.

## Architecture Overview

```
SAAS_MultiTenent_SSO/
├── backend/              # Laravel 10 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # Base controller + Health + Webhook
│   │   │   └── Middleware/     # TenantMiddleware, AbacMiddleware
│   │   ├── Modules/
│   │   │   ├── User/           # Auth + User CRUD
│   │   │   ├── Tenant/         # Multi-tenancy models
│   │   │   ├── Product/        # Product CRUD + Events
│   │   │   ├── Inventory/      # Inventory management + Stock alerts
│   │   │   └── Order/          # Order management + Saga pattern
│   │   └── Providers/          # App, Auth, Event service providers
│   ├── config/                 # App, Auth, Queue config
│   ├── database/
│   │   ├── migrations/         # All schema migrations
│   │   └── seeders/            # Roles, Permissions, Default tenant/users
│   ├── routes/                 # API + Web + Console routes
│   └── tests/                  # Feature tests
├── frontend/             # React + TypeScript + Vite
│   └── src/
│       ├── context/            # AuthContext with SSO token management
│       ├── services/           # API clients per module
│       ├── pages/              # Dashboard, Products, Inventory, Orders, Users
│       ├── components/         # Layout, Pagination, ProtectedRoute
│       └── types/              # TypeScript type definitions
└── docker-compose.yml    # Full stack orchestration
```

## Modules

Each module follows the **Controller → Service → Repository** pattern and contains:
- **Controllers** – HTTP request handling only
- **Services** – Business logic and event orchestration
- **Repositories** – Database access via Eloquent (interface + implementation)
- **DTOs** – Clean data transfer objects
- **Events/Listeners** – Domain events (queued via RabbitMQ)
- **Form Requests** – Input validation
- **API Resources** – Consistent JSON response formatting
- **Webhooks** – Structured webhook payload handling
- **Routes** – Module-scoped route files

## Key Features

### Authentication & SSO
- **Laravel Passport** OAuth2 with personal access tokens
- Token-based SSO across React frontend and all API endpoints
- Token refresh and revocation support

### Multi-Tenancy
- Every resource is scoped to a `tenant_id`
- `TenantMiddleware` enforces that users can only access their own tenant's data
- Super-admins bypass tenant restrictions

### RBAC/ABAC
- **Roles**: `super-admin`, `admin`, `manager`, `user`
- **16 Permissions**: create/edit/delete/view for each of users, products, inventory, orders
- **ABAC**: `AbacMiddleware` checks user `attributes` JSON for fine-grained access control

### Event-Driven Architecture
- `ProductCreated` → automatically creates an `Inventory` record
- `ProductDeleted` → removes the associated inventory
- `OrderCreated` → reserves inventory quantities (Saga step)
- `OrderCancelled` → releases reserved quantities (compensating transaction)
- `LowStockAlert` → triggered when quantity drops below minimum
- All listeners implement `ShouldQueue` for async processing via **RabbitMQ**

### ACID Transactions + Saga Pattern
- All service methods wrap operations in `DB::transaction()`
- Order creation validates inventory before any writes
- Order cancellation fires `OrderCancelled` event which releases reserved inventory (compensating transaction)

### Advanced API Features
- Pagination (configurable `per_page`)
- Filtering (search, category, status, price range, etc.)
- Sorting (`sort_by`, `sort_dir`)
- Consistent JSON via API Resources

## Quick Start

### Prerequisites
- Docker and Docker Compose

### Run with Docker
```bash
git clone <repo>
cd SAAS_MultiTenent_SSO
docker-compose up -d
```

Services:
- **Backend API**: http://localhost:8000
- **Frontend**: http://localhost:3000
- **RabbitMQ Management**: http://localhost:15672 (guest/guest)
- **MySQL**: localhost:3306

### Default Credentials
| Email | Password | Role |
|-------|----------|------|
| superadmin@example.com | password | super-admin |
| admin@example.com | password | admin |

## API Endpoints

### Auth
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout         (requires auth)
GET  /api/auth/me             (requires auth)
POST /api/auth/refresh        (requires auth)
```

### Users (requires auth + tenant middleware)
```
GET    /api/users
POST   /api/users             (permission: create-users)
GET    /api/users/{id}
PUT    /api/users/{id}        (permission: edit-users)
DELETE /api/users/{id}        (permission: delete-users)
```

### Products
```
GET    /api/products
POST   /api/products          (permission: create-products)
GET    /api/products/{id}
PUT    /api/products/{id}     (permission: edit-products)
DELETE /api/products/{id}     (permission: delete-products)
```

### Inventory
```
GET    /api/inventory
POST   /api/inventory         (permission: create-inventory)
GET    /api/inventory/{id}
PUT    /api/inventory/{id}    (permission: edit-inventory)
DELETE /api/inventory/{id}    (permission: delete-inventory)
POST   /api/inventory/{id}/adjust (permission: edit-inventory)
```

### Orders
```
GET    /api/orders
POST   /api/orders            (permission: create-orders)
GET    /api/orders/{id}
PATCH  /api/orders/{id}/status (permission: edit-orders)
DELETE /api/orders/{id}       (permission: delete-orders)
```

### Health & Webhooks
```
GET  /api/health
GET  /up
POST /api/webhooks/users
POST /api/webhooks/products
```

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 10 |
| Authentication | Laravel Passport (OAuth2) |
| Authorization | spatie/laravel-permission (RBAC) + Custom ABAC |
| Database | MySQL 8.0 |
| Cache | Redis 7 |
| Message Broker | RabbitMQ 3.12 |
| Queue Driver | vladimir-yuldashev/laravel-queue-rabbitmq |
| Frontend | React 18 + TypeScript + Vite |
| Routing (Frontend) | React Router v6 |
| HTTP Client | Axios |
| Containerization | Docker + Docker Compose |
