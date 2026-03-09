# Enterprise Inventory Management System (IMS)

A production-ready, enterprise-grade **multi-tenant SaaS** Inventory Management System built with a **microservice architecture**, demonstrating distributed transactions using the **Saga Pattern**, **Domain-Driven Design (DDD)**, and **Clean Architecture** principles.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         API Gateway (Nginx)                       │
│                    Routes /api/* to microservices                 │
└─────┬──────────┬──────────┬──────────┬──────────────────────────┘
      │          │          │          │
      ▼          ▼          ▼          ▼
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────┐
│   Auth   │ │Inventory │ │  Order   │ │   Payment    │
│ Service  │ │ Service  │ │ Service  │ │   Service    │
│(Laravel) │ │(Laravel) │ │(Laravel) │ │  (Node.js)   │
│  :8001   │ │  :8002   │ │  :8003   │ │    :8004     │
│          │ │          │ │          │ │              │
│ MySQL    │ │ MySQL    │ │ MySQL    │ │ PostgreSQL   │
└──────────┘ └──────────┘ └──────────┘ └──────────────┘
      │          │          │          │
      └──────────┴──────────┴──────────┘
                          │
              ┌───────────┴───────────┐
              │    Message Broker     │
              │  RabbitMQ | Kafka     │
              │  (pluggable driver)   │
              └───────────────────────┘
```

---

## Key Features

### Architecture & Design Patterns
- **Microservices**: Each service is independently deployable with its own database
- **DDD (Domain-Driven Design)**: Bounded contexts, entities, repositories, services
- **Clean Architecture**: Controller → Service → Repository with thin controllers
- **SOLID + DRY + KISS**: Enforced throughout all services
- **API-First Design**: Contract-first REST APIs with full versioning

### Multi-Tenant SaaS
- **Strict tenant isolation**: All data is tenant-scoped
- **Runtime configuration**: Change tenant settings without restart (database, mail, cache, broker, service URLs)
- **Tenant resolution**: Subdomain, X-Tenant-ID header, JWT claim, query param

### Authentication & Authorization
- **Laravel Passport SSO**: OAuth2 / JWT token management
- **Multi-guard**: Per-user, per-device, per-organization tokens
- **RBAC**: Role-Based Access Control with tenant-scoped roles
- **ABAC**: Attribute-Based Access Control with dynamic policies stored in DB

### Base Repository (Reusable)
- **Conditional pagination**: Returns `LengthAwarePaginator` when `per_page` present, `Collection` otherwise
- **Filtering**: Column-level filters, range filters (`column_from`/`column_to`)
- **Full-text search**: Across configurable `searchableColumns`
- **Sorting**: Safe column validation (prevents SQL injection)
- **Cross-service pagination**: `paginateCollection()` works on arrays, Collections, API responses

### Saga Pattern (Distributed Transactions)
```
Place Order Saga:
1. Reserve Inventory  (Inventory Service) → compensate: Release reservation
2. Process Payment    (Payment Service)   → compensate: Refund
3. Confirm Order      (Order Service)     → compensate: Cancel order

On any failure → compensating transactions run in reverse order
```

### Pluggable Message Broker
```bash
MESSAGE_BROKER_DRIVER=rabbitmq  # or kafka - no code change needed
```

---

## Tech Stack

| Service          | Language  | Framework  | Database    |
|------------------|-----------|------------|-------------|
| Auth Service     | PHP 8.3   | Laravel 11 | MySQL 8     |
| Inventory Service| PHP 8.3   | Laravel 11 | MySQL 8     |
| Order Service    | PHP 8.3   | Laravel 11 | MySQL 8     |
| Payment Service  | Node.js 20| Express 4  | PostgreSQL 15|

---

## Quick Start

```bash
# 1. Configure environment
cp .env.example .env  # Fill in secrets

# 2. Start services
docker compose up -d

# 3. Run migrations
docker compose exec auth-service php artisan migrate --seed
docker compose exec inventory-service php artisan migrate --seed
docker compose exec order-service php artisan migrate --seed
docker compose exec payment-service node src/database/migrate.js

# 4. Verify
curl http://localhost/api/health
```

---

## File Structure

```
KV/
├── docker-compose.yml              # Multi-service orchestration
├── .env.example                    # Environment template
├── docker/                         # Nginx, MySQL, RabbitMQ configs
├── shared/contracts/               # Shared PHP interfaces (Repo, Broker, Saga, Tenant)
└── services/
    ├── auth-service/               # Laravel: Auth, Tenant, RBAC/ABAC, Passport SSO
    ├── inventory-service/          # Laravel: Inventory, DDD, Webhooks
    ├── order-service/              # Laravel: Orders, Saga Orchestration
    └── payment-service/            # Node.js/Express: Payments (different tech stack)
```

---

## API Examples

```bash
# Login
POST /api/v1/auth/login
X-Tenant-ID: {tenant-uuid}
{ "email": "admin@co.com", "password": "secret" }

# List inventory (conditional pagination)
GET /api/v1/inventory?per_page=20&page=1&search=laptop&sort_by=name

# Create order (triggers distributed Saga)
POST /api/v1/orders
{
  "customer_id": "uuid",
  "items": [{ "inventory_item_id": "uuid", "sku": "ABC", "name": "Widget", "quantity": 2, "unit_price": 9.99 }],
  "payment_method": { "type": "credit_card", "token": "tok_visa" }
}

# Update tenant runtime config (no restart)
PATCH /api/v1/tenants/{id}/configuration
{ "configuration": { "mail": { "driver": "mailgun" }, "message_broker": { "driver": "kafka" } } }
```

---

## Testing

```bash
# Laravel unit/feature tests
docker compose exec auth-service php artisan test
docker compose exec order-service php artisan test --filter SagaOrchestratorTest

# Node.js tests
docker compose exec payment-service npm test
```

---

## Design Principles

- **SOLID**: Each class has one responsibility; all dependencies injected through interfaces
- **DRY**: BaseRepository reused across all services; MessageBrokerFactory shared across PHP and Node.js
- **KISS**: Thin controllers (~30 lines), services own one domain concern, saga steps are isolated units
- **Clean Architecture**: Domain layer has zero framework dependencies; infrastructure implements interfaces

## License

MIT
