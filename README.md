# Laravel SaaS Multi-Tenant Platform

A production-ready, microservices-based multi-tenant SaaS platform built with Laravel 10, React, Docker, RabbitMQ, and Redis.

---

## Architecture Overview

```
┌────────────────────────────────────────────────────────────────┐
│                        Nginx (80 / 443)                        │
└────────────────────────┬───────────────────────────────────────┘
                         │
              ┌──────────▼──────────┐
              │   API Gateway :8000  │  JWT validation, routing,
              │                     │  rate limiting, tenant lookup
              └──┬──┬──┬──┬──┬─────┘
                 │  │  │  │  │
     ┌───────────┘  │  │  │  └──────────────┐
     │         ┌────┘  │  └────┐            │
     ▼         ▼       ▼       ▼            ▼
  Auth      Tenant  Inventory Order   Notification
 :8001      :8002    :8003    :8004      :8005
     │         │       │       │            │
     └────┬────┴───────┴───────┴────────────┘
          │               │
     ┌────▼───┐      ┌────▼────┐
     │ MySQL  │      │RabbitMQ │  ← async domain events
     │  :3306 │      │  :5672  │
     └────────┘      └─────────┘
          │
     ┌────▼───┐
     │ Redis  │  ← sessions, cache, queues
     │  :6379 │
     └────────┘
```

### Services

| Service              | Port | Description                                       |
|----------------------|------|---------------------------------------------------|
| **api-gateway**      | 8000 | Single entry point; JWT auth, routing             |
| **auth-service**     | 8001 | Registration, login, token issuance/revocation    |
| **tenant-service**   | 8002 | Tenant CRUD, plan management, onboarding          |
| **inventory-service**| 8003 | SKU management, stock levels, reservations        |
| **order-service**    | 8004 | Order lifecycle, payment hooks                    |
| **notification-service** | 8005 | Email / push / webhook notifications         |
| **frontend**         | 3000 | React SPA                                         |
| **nginx**            | 80/443 | TLS termination, static assets               |
| **mysql**            | 3306 | Per-service databases                             |
| **redis**            | 6379 | Cache, sessions, queues                           |
| **rabbitmq**         | 5672 / 15672 | Async inter-service messaging          |

---

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) >= 24.x
- [Docker Compose](https://docs.docker.com/compose/) >= 2.20
- (Optional) PHP 8.2, Composer 2, Node 20 for local development without Docker

---

## Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/your-org/Laravel_SAAS_MultiTenent.git
cd Laravel_SAAS_MultiTenent

# 2. Configure environment
cp .env.example .env
# Edit .env and set strong secrets (JWT_SECRET, DB passwords, APP_KEYs)

# 3. Start all services
docker compose up -d --build

# 4. Run migrations for each service
docker compose exec inventory-service php artisan migrate --force
docker compose exec auth-service     php artisan migrate --force
docker compose exec tenant-service   php artisan migrate --force
docker compose exec order-service    php artisan migrate --force

# 5. Open the app
open http://localhost:3000        # React frontend
open http://localhost:8000/health # API Gateway health
open http://localhost:15672       # RabbitMQ Management UI (guest/guest)
```

---

## Multi-Tenancy Explanation

Every service implements **header-based tenant isolation**:

1. The client sends `X-Tenant-ID: <uuid>` with every request.
2. The API Gateway validates the JWT and verifies the tenant is active (via `tenant-service` cache).
3. Each downstream service extracts `tenant_id` from the header in `TenantMiddleware` and scopes **all database queries** to that tenant using Eloquent global scopes / repository methods.
4. Data is isolated at the **row level** — each table has a `tenant_id` column with an index. A future upgrade path to per-tenant schema or per-tenant database is supported by the repository abstraction.

---

## API Endpoints

### Auth Service (`/api/v1/auth/...`)
| Method | Path | Description |
|--------|------|-------------|
| POST | `/register` | Register new user |
| POST | `/login` | Obtain JWT tokens |
| POST | `/refresh` | Refresh access token |
| POST | `/logout` | Revoke token |
| GET  | `/me` | Current user profile |

### Tenant Service (`/api/v1/tenants/...`)
| Method | Path | Description |
|--------|------|-------------|
| POST | `/` | Create tenant |
| GET  | `/{id}` | Get tenant details |
| PUT  | `/{id}` | Update tenant |
| DELETE | `/{id}` | Delete tenant |

### Inventory Service (`/api/v1/inventories/...`)
| Method | Path | Description |
|--------|------|-------------|
| GET  | `/` | List inventory (filterable, paginated) |
| POST | `/` | Create inventory item |
| GET  | `/{id}` | Get item |
| PUT  | `/{id}` | Update item |
| DELETE | `/{id}` | Soft delete item |
| POST | `/{id}/adjust-stock` | Increment / decrement stock |
| GET  | `/reports/low-stock` | Items below minimum stock level |
| GET  | `/health` | Service health check |

### Order Service (`/api/v1/orders/...`)
| Method | Path | Description |
|--------|------|-------------|
| GET  | `/` | List orders |
| POST | `/` | Create order |
| GET  | `/{id}` | Get order |
| PUT  | `/{id}/status` | Update order status |

---

## Development Guide

### Running a single service locally

```bash
cd services/inventory-service
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve --port=8003
```

### Running tests

```bash
docker compose exec inventory-service php artisan test
```

### Viewing logs

```bash
docker compose logs -f inventory-service
```

### Rebuilding a single service

```bash
docker compose up -d --build inventory-service
```

---

## Environment Variables

See `.env.example` for the full list. Key variables:

| Variable | Description |
|----------|-------------|
| `JWT_SECRET` | Shared secret used by all services to verify JWTs |
| `MYSQL_ROOT_PASSWORD` | MySQL root password |
| `REDIS_PASSWORD` | Redis AUTH password |
| `RABBITMQ_USER/PASSWORD` | RabbitMQ credentials |
| `MESSAGE_BROKER` | `rabbitmq` or `kafka` |
| `TENANT_HEADER` | HTTP header carrying the tenant ID (default `X-Tenant-ID`) |

---

## License

MIT
