# Multi-Tenant SaaS Inventory Management System

A fully dynamic, extendible, and reusable **multi-tenant SaaS Inventory Management System** built with:
- **Laravel 11** backend (modular microservices, Controller → Service → Repository)
- **React + TypeScript** frontend (Vite + Tailwind CSS)
- **Laravel Passport** for SSO / OAuth2 authentication
- **Spatie Laravel Permission** for RBAC + ABAC authorization
- **Saga pattern** for distributed transaction management
- **Pluggable MessageBroker** (Null / RabbitMQ / Apache Kafka)
- **Docker Compose** for full-stack orchestration

---

## Architecture

```
┌─────────────────────────────────────────────────────┐
│                   React Frontend                     │
│   Login/SSO ─ Users ─ Products ─ Inventory ─ Orders │
└────────────────────┬────────────────────────────────┘
                     │ HTTP/REST + X-Tenant header
┌────────────────────▼────────────────────────────────┐
│              Laravel API (Passport SSO)             │
│                                                     │
│  ┌──────────┐ ┌──────────┐ ┌───────────┐ ┌───────┐ │
│  │   Auth   │ │  Users   │ │ Products  │ │Tenant │ │
│  │ Module   │ │  Module  │ │  Module   │ │Config │ │
│  └────┬─────┘ └────┬─────┘ └─────┬─────┘ └───┬───┘ │
│       │            │             │            │     │
│  ┌────┴─────────────┴─────────────┴────────────┘    │
│  │          Inventory Module (cross-service)         │
│  │          Order Module (Saga pattern)              │
│  └──────────────────────────────────────────────────│
│                                                     │
│  Core Infrastructure:                               │
│  • TenantManager + TenantScope (global Eloquent)    │
│  • PaginationHelper (conditional per_page/page)     │
│  • SagaOrchestrator (compensating transactions)     │
│  • MessageBrokerInterface (Null/RabbitMQ/Kafka)     │
│  • PolicyManager (RBAC + ABAC)                      │
└─────────────────────────────────────────────────────┘
```

## Modules

| Module    | Endpoints | Key Features |
|-----------|-----------|-------------|
| **Auth**  | `/api/auth/*` | Login, Register, Logout, Refresh, `/me`, SSO Token |
| **User**  | `/api/users/*` | CRUD + Role assign/revoke, RBAC/ABAC, tenant-scoped |
| **Product** | `/api/products/*` | CRUD + search (name/SKU/category/price) |
| **Inventory** | `/api/inventory/*` | CRUD + adjust/reserve/release qty, cross-service product-name filter |
| **Order** | `/api/orders/*` | Place via Saga (validate→reserve→create→confirm), cancel w/ rollback |
| **Tenant Config** | `/api/tenant/config` | Per-tenant runtime settings (mail, payment, etc.) |

## Quick Start (local)

```bash
# 1. Backend
cd backend
cp .env.example .env
php artisan key:generate
composer install
php artisan migrate --seed
php artisan passport:client --personal --no-interaction
php artisan serve

# 2. Frontend
cd frontend
cp .env.example .env
npm install
npm run dev
```

### Demo credentials
| Email | Password | Tenant | Role |
|-------|----------|--------|------|
| admin@demo.com | password | demo | admin |
| manager@demo.com | password | demo | manager |
| user@demo.com | password | demo | user |

## Docker Compose

```bash
# Basic stack (backend + frontend + MySQL)
docker compose up -d

# With RabbitMQ
MESSAGE_BROKER_DRIVER=rabbitmq docker compose --profile rabbitmq up -d

# With Kafka
MESSAGE_BROKER_DRIVER=kafka docker compose --profile kafka up -d
```

## Key Design Decisions

### Multi-Tenancy
Every tenant-scoped model uses the `HasTenant` trait which registers a `TenantScope` global scope, ensuring all queries are automatically filtered to the current tenant. The tenant is resolved from:
1. `X-Tenant` HTTP header
2. Authenticated user's tenant relation
3. Subdomain
4. `?tenant=` query param (local/test only)

### Conditional Pagination (`PaginationHelper`)
```php
// Returns ALL results
GET /api/products

// Returns paginated results with meta + links
GET /api/products?per_page=10&page=2
```
Works with Eloquent Builder, Collections, and plain arrays.

### Saga Pattern (Order placement)
```
1. ValidateOrderStep    – verify products & compute totals
2. ReserveInventoryStep – reserve stock (compensate: release reservation)
3. CreateOrderStep      – persist order + items (compensate: delete order)
4. ConfirmOrderStep     – deduct actual stock & mark confirmed
```
If any step fails, all completed steps run their `compensate()` method in reverse order.

### MessageBroker Interface
```php
// Switch broker via .env – no code changes needed
MESSAGE_BROKER_DRIVER=rabbitmq   # or kafka, or null (default)
```

### RBAC + ABAC
- **RBAC**: Spatie `laravel-permission` (roles: admin, manager, user)
- **ABAC**: `PolicyManager` checks `resource->tenant_id === user->tenant_id` for tenant isolation
- Middleware: `abac:product.view` for route-level enforcement

