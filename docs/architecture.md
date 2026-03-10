# Microservices Architecture Documentation

## Overview

This system demonstrates a **production-grade microservices architecture** centered around Laravel, showcasing how independent services collaborate in a loosely coupled, highly scalable system. Each service owns its own technology stack and database, communicating through well-defined interfaces.

---

## Service Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         API Gateway (Node.js)                        │
│                    Port: 8000  |  Rate-limiting, JWT auth            │
└────────┬─────────────┬──────────────┬──────────────────┬────────────┘
         │             │              │                  │
         ▼             ▼              ▼                  ▼
  ┌────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
  │    User    │ │   Product    │ │    Order     │ │   Payment    │
  │  Service   │ │   Service    │ │   Service    │ │   Service    │
  │  (Lumen)   │ │  (Node.js)   │ │  (Lumen)     │ │  (Python)    │
  │  Port 8001 │ │  Port 8002   │ │  Port 8003   │ │  Port 8004   │
  └─────┬──────┘ └──────┬───────┘ └──────┬───────┘ └──────┬───────┘
        │               │                │                 │
        ▼               ▼                ▼                 ▼
   ┌─────────┐    ┌──────────┐    ┌──────────┐    ┌──────────────┐
   │  MySQL  │    │ MongoDB  │    │  MySQL   │    │ PostgreSQL   │
   │users_db │    │products  │    │orders_db │    │payments_db   │
   └─────────┘    └──────────┘    └──────────┘    └──────────────┘

                         ┌─────────────────┐
                         │    RabbitMQ     │
                         │  (Message Broker│
                         │   Port 5672)    │
                         └────────┬────────┘
                                  │
          ┌───────────────────────┼────────────────────────┐
          ▼                       ▼                         ▼
  Product Service           Order Service            Payment Service
  (inventory events)      (saga coordinator)        (payment events)
```

---

## Technology Stack

| Service          | Language  | Framework     | Database    | Communication         |
|------------------|-----------|---------------|-------------|----------------------|
| API Gateway      | Node.js   | Express       | -           | REST (proxy)         |
| User Service     | PHP       | Lumen (Laravel)| MySQL       | REST                 |
| Product Service  | Node.js   | Express       | MongoDB     | REST + AMQP          |
| Order Service    | PHP       | Lumen (Laravel)| MySQL       | REST + AMQP          |
| Payment Service  | Python    | FastAPI       | PostgreSQL  | REST + AMQP          |
| Message Broker   | -         | RabbitMQ      | -           | AMQP/Topic Exchange  |

---

## Service Responsibilities

### API Gateway (Node.js Express)
- **Single entry point** for all client requests
- **JWT validation** — verifies tokens before forwarding requests
- **Rate limiting** — prevents abuse (200 req/15 min)
- **Request routing** — forwards to appropriate downstream service
- **Error handling** — returns 502 if downstream is unavailable

### User Service (Lumen + MySQL)
- **User registration** with hashed passwords (bcrypt)
- **JWT issuance** via `firebase/php-jwt` (HS256)
- **Authentication** — returns signed JWT tokens
- **User profile management** (CRUD)
- **Owns**: `users_db` (MySQL)

### Product Service (Node.js Express + MongoDB)
- **Product catalog** — CRUD for products
- **Inventory management** — tracks stock and reserved quantities
- **Saga participant** — listens for `order.created` events
- **Atomic reservation** — uses MongoDB atomic update for race-free inventory reservation
- **Compensation** — listens for `inventory.release` to undo reservations
- **Owns**: `products_db` (MongoDB)

### Order Service (Lumen + MySQL, Saga Orchestrator)
- **Order management** — create, view, cancel orders
- **Saga coordinator** — publishes events and monitors saga progress
- **Status tracking** — maintains `saga_state` field throughout the saga
- **Compensation trigger** — on payment failure, triggers `inventory.release`
- **Owns**: `orders_db` (MySQL)

### Payment Service (Python FastAPI + PostgreSQL)
- **Payment processing** — simulates payment gateway calls
- **Saga participant** — listens for `inventory.reserved` events
- **Async consumer** — uses `aio-pika` for async AMQP processing
- **Payment records** — stores all payment attempts
- **Owns**: `payments_db` (PostgreSQL)

---

## Interaction Flows

### 1. User Registration + Login (Synchronous REST)

```
Client → POST /api/users/register → API Gateway → User Service
                                                        │
                                              Save user to MySQL
                                                        │
                                              Return JWT token
                                                        │
Client ← 201 { token, user } ─────────────────────────┘
```

### 2. Browse Products (Synchronous REST)

```
Client → GET /api/products → API Gateway → Product Service
                             (JWT check)         │
                                           Fetch from MongoDB
                                                  │
Client ← 200 { products } ────────────────────────┘
```

### 3. Place Order – Saga Pattern (Asynchronous Choreography)

```
Client → POST /api/orders → API Gateway → Order Service
                            (JWT check)        │
                                        Create order (status=PENDING)
                                               │
                                     Publish order.created → RabbitMQ
                                               │
                                               ▼
                                    ┌─── RabbitMQ Exchange ───┐
                                    │      (saga.events)       │
                                    └──────────┬───────────────┘
                                               │ order.created
                                               ▼
                                       Product Service
                                               │
                               Check: stock - reserved >= quantity ?
                                       ┌──────┴──────┐
                                      YES            NO
                                       │              │
                              reserve inventory    publish
                                       │        inventory.reservation.failed
                              publish inventory.reserved
                                       │
                                       ▼
                               Payment Service
                                       │
                               Process payment
                                ┌──────┴──────┐
                               SUCCESS       FAILURE
                                │              │
                        publish payment.     publish payment.
                        processed            failed
                                │              │
                                ▼              ▼
                          Order Service   Order Service
                                │              │
                      status=COMPLETED   Publish inventory.release
                                         status=FAILED
                                               │
                                               ▼
                                       Product Service
                                               │
                                    Release reserved inventory
                                    (compensation/rollback)
```

---

## Distributed Transaction: Saga Pattern

### Pattern Type: **Choreography-based Saga**

In a choreography-based Saga, there is **no central orchestrator**. Instead, each service **reacts to events** and publishes new events, creating a chain of reactions.

### Events Published

| Event                          | Publisher        | Subscribers            |
|-------------------------------|------------------|------------------------|
| `order.created`               | Order Service    | Product Service        |
| `inventory.reserved`          | Product Service  | Payment Service, Order Service |
| `inventory.reservation.failed`| Product Service  | Order Service          |
| `payment.processed`           | Payment Service  | Order Service          |
| `payment.failed`              | Payment Service  | Order Service          |
| `inventory.release`           | Order Service    | Product Service        |

### Saga States (Order entity)

```
order_created
    │
    ├─► inventory_reserved ──► (payment_service_processing)
    │                               │
    │                        ┌──────┴──────┐
    │                   payment_processed  payment_failed_compensated
    │                   (COMPLETED)        (FAILED)
    │
    └─► inventory_reservation_failed
        (FAILED)
```

---

## RabbitMQ Exchange Configuration

```
Exchange: saga.events
Type: topic (supports pattern-based routing)
Durable: true

Bindings:
  Queue: product.reserve         ← routing key: order.created
  Queue: product.release         ← routing key: inventory.release
  Queue: payment.process         ← routing key: inventory.reserved
  Queue: order.inventory.reserved           ← routing key: inventory.reserved
  Queue: order.inventory.reservation.failed ← routing key: inventory.reservation.failed
  Queue: order.payment.processed            ← routing key: payment.processed
  Queue: order.payment.failed               ← routing key: payment.failed
```

---

## Failure Handling & Rollback

### Scenario 1: Insufficient Stock

```
order.created published
    ↓
Product Service: stock check FAILS
    ↓
inventory.reservation.failed published
    ↓
Order Service: order.status = FAILED
              order.failure_reason = "Insufficient stock"
              order.saga_state = "inventory_reservation_failed"

→ No compensation needed (no state was changed)
```

### Scenario 2: Payment Declined

```
order.created published
    ↓
Product Service: inventory reserved (stock decremented)
    ↓
inventory.reserved published
    ↓
Payment Service: payment DECLINED
    ↓
payment.failed published
    ↓
Order Service:
  1. Publishes inventory.release (compensation)
  2. Updates order.status = FAILED
     order.saga_state = "payment_failed_compensated"
    ↓
Product Service: inventory.release received
  → reserved quantity decremented (rollback)
```

### Scenario 3: Service Unavailable

- **RabbitMQ messages are durable** — messages survive broker restarts
- **Queues are durable** — messages are not lost if a consumer is down
- **Retry logic** — consumers reconnect automatically on failure
- **Dead-letter queues** can be added for messages that fail repeatedly (future enhancement)

---

## Scalability

### Horizontal Scaling

Each service is **stateless** (state stored in its own database) and can be scaled independently:

```bash
# Scale product service to 3 instances
docker compose up --scale product_service=3

# Scale payment service to 2 instances
docker compose up --scale payment_service=2
```

RabbitMQ queues use **competing consumer** pattern — multiple instances of the same service will process messages in parallel without duplication.

### Vertical Scaling

Each service can independently increase CPU/memory resources via Docker Compose `deploy.resources` settings.

### Database Scaling

- **MySQL** (User/Order): Read replicas via `DB_READ_HOST` env var
- **MongoDB** (Product): Replica sets and sharding
- **PostgreSQL** (Payment): Read replicas and connection pooling (PgBouncer)

---

## API Reference

### User Service (`/api/users`)

| Method | Path              | Auth | Description          |
|--------|-------------------|------|----------------------|
| POST   | /register         | No   | Register new user    |
| POST   | /login            | No   | Login, get JWT       |
| GET    | /me               | Yes  | Get own profile      |
| GET    | /{id}             | Yes  | Get user by ID       |
| PUT    | /{id}             | Yes  | Update user profile  |

### Product Service (`/api/products`)

| Method | Path              | Auth | Description          |
|--------|-------------------|------|----------------------|
| GET    | /                 | Yes  | List products        |
| GET    | /{id}             | Yes  | Get product by ID    |
| POST   | /                 | Yes  | Create product       |
| PUT    | /{id}             | Yes  | Update product       |
| DELETE | /{id}             | Yes  | Delete product       |

### Order Service (`/api/orders`)

| Method | Path              | Auth | Description          |
|--------|-------------------|------|----------------------|
| GET    | /                 | Yes  | List my orders       |
| POST   | /                 | Yes  | Create order (Saga)  |
| GET    | /{id}             | Yes  | Get order by ID      |
| PATCH  | /{id}/cancel      | Yes  | Cancel pending order |

### Payment Service (`/api/payments`)

| Method | Path                    | Auth | Description              |
|--------|-------------------------|------|--------------------------|
| GET    | /                       | Yes  | List payments            |
| GET    | /{id}                   | Yes  | Get payment by ID        |
| GET    | /order/{order_id}       | Yes  | Get payments for order   |
| POST   | /                       | Yes  | Create payment (manual)  |

---

## Running the System

### Prerequisites
- Docker Engine 24+
- Docker Compose v2+

### Quick Start

```bash
# Clone and start all services
git clone https://github.com/kasunvimarshana/KV_SSO_SAAS.git
cd KV_SSO_SAAS

docker compose up -d --build

# Wait ~30s for all services to be healthy
docker compose ps
```

### Demo Workflow

```bash
BASE_URL="http://localhost:8000"

# 1. Register a user
curl -X POST $BASE_URL/api/users/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com","password":"password123"}'

# 2. Login and get token
TOKEN=$(curl -s -X POST $BASE_URL/api/users/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password123"}' | jq -r .token)

# 3. Create a product
PRODUCT_ID=$(curl -s -X POST $BASE_URL/api/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Widget","price":29.99,"stock":100}' | jq -r .product._id)

# 4. Place an order (triggers Saga)
curl -X POST $BASE_URL/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"product_id\":\"$PRODUCT_ID\",\"quantity\":2}"

# 5. Check order status (poll until completed/failed)
curl -H "Authorization: Bearer $TOKEN" $BASE_URL/api/orders/1
```

### Monitoring

- **RabbitMQ Management UI**: http://localhost:15672 (guest/guest)
- **API Gateway Health**: http://localhost:8000/health
- **Service Logs**: `docker compose logs -f <service_name>`
