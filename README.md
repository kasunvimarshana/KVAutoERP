# KV Microservices System

A complete **microservices-based example system** centered around **Laravel** that demonstrates how independent services collaborate in a loosely coupled, highly scalable architecture. Services use different technology stacks and databases, communicate via REST and asynchronous message queues, and implement the **Saga pattern** for distributed transaction management.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                     API Gateway (Node.js Express)                    │
│              Port 8000 │ JWT Auth │ Rate Limiting │ Routing          │
└────────┬──────────────┬──────────────┬─────────────────┬────────────┘
         │              │              │                 │
         ▼              ▼              ▼                 ▼
  ┌────────────┐ ┌─────────────┐ ┌──────────────┐ ┌─────────────┐
  │    User    │ │   Product   │ │    Order     │ │   Payment   │
  │  Service   │ │   Service   │ │   Service    │ │   Service   │
  │ PHP/Lumen  │ │  Node.js    │ │  PHP/Lumen   │ │   Python    │
  │  Port 8001 │ │  Port 8002  │ │  Port 8003   │ │  Port 8004  │
  │   MySQL    │ │  MongoDB    │ │    MySQL     │ │ PostgreSQL  │
  └────────────┘ └─────────────┘ └──────┬───────┘ └──────┬──────┘
                                         │                 │
                                         └────────┬────────┘
                                                  │
                                         ┌────────▼────────┐
                                         │    RabbitMQ     │
                                         │  Message Broker │
                                         │   Port 5672     │
                                         └─────────────────┘
```

---

## Technology Stack

| Service         | Language | Framework      | Database   | Port |
|-----------------|----------|----------------|------------|------|
| API Gateway     | Node.js  | Express        | –          | 8000 |
| User Service    | PHP 8.2  | Lumen (Laravel)| MySQL 8.0  | 8001 |
| Product Service | Node.js  | Express        | MongoDB 7  | 8002 |
| Order Service   | PHP 8.2  | Lumen (Laravel)| MySQL 8.0  | 8003 |
| Payment Service | Python   | FastAPI        | PostgreSQL | 8004 |
| Message Broker  | –        | RabbitMQ 3.12  | –          | 5672 |

---

## Key Features

- ✅ **Polyglot persistence** — each service owns its own database
- ✅ **Independent deployability** — each service runs in its own Docker container
- ✅ **JWT authentication** — issued by User Service, validated at API Gateway
- ✅ **REST APIs** — synchronous service-to-service and client-to-service communication
- ✅ **Message queues** — asynchronous communication via RabbitMQ topic exchange
- ✅ **Choreography-based Saga** — distributed transaction management without a central orchestrator
- ✅ **Compensating transactions** — automatic rollback when a saga step fails
- ✅ **Horizontal scalability** — stateless services with competing consumer queues
- ✅ **Rate limiting** — at the API Gateway level

---

## Saga Pattern: Order Processing Flow

The system uses a **Choreography-based Saga** for the order-to-payment workflow:

```
Client
  │
  ├─► POST /api/orders
  │         │
  │   Order Service creates order (status=PENDING)
  │   Publishes: order.created
  │
  │   ───────────────────────────────────────────────
  │
  │   Product Service receives: order.created
  │     ├─► [STOCK OK]  reserves inventory
  │     │   Publishes: inventory.reserved
  │     │
  │     └─► [NO STOCK]  Publishes: inventory.reservation.failed
  │                       → Order Service: status=FAILED ✗
  │
  │   ───────────────────────────────────────────────
  │
  │   Payment Service receives: inventory.reserved
  │     ├─► [PAYMENT OK]  Publishes: payment.processed
  │     │   → Order Service: status=COMPLETED ✓
  │     │
  │     └─► [PAYMENT FAIL]  Publishes: payment.failed
  │           → Order Service:
  │               1. Publishes: inventory.release (compensation)
  │               2. status=FAILED ✗
  │
  │   Product Service receives: inventory.release
  │     → Releases reserved inventory (rollback) ↩
  │
  └─► GET /api/orders/1  (poll for final status)
```

---

## Quick Start

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/) ≥ 24.0
- [Docker Compose](https://docs.docker.com/compose/) v2+

### Start All Services

```bash
git clone https://github.com/kasunvimarshana/KV_SSO_SAAS.git
cd KV_SSO_SAAS

docker compose up -d --build
```

All services start automatically with health checks and database migrations.

### Health Checks

```bash
curl http://localhost:8000/health          # API Gateway
curl http://localhost:8001/health          # User Service
curl http://localhost:8002/health          # Product Service
curl http://localhost:8003/health          # Order Service
curl http://localhost:8004/health          # Payment Service
```

---

## Demo Walkthrough

```bash
BASE_URL="http://localhost:8000"

# 1. Register a user
curl -s -X POST $BASE_URL/api/users/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com","password":"password123"}' | jq

# 2. Login and extract JWT token
TOKEN=$(curl -s -X POST $BASE_URL/api/users/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password123"}' | jq -r .token)

echo "Token: $TOKEN"

# 3. Create a product with 100 units stock
PRODUCT_ID=$(curl -s -X POST $BASE_URL/api/products \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Widget Pro","description":"A great widget","price":29.99,"stock":100,"category":"electronics"}' \
  | jq -r '.product._id')

echo "Product ID: $PRODUCT_ID"

# 4. Place an order (triggers the Saga)
ORDER=$(curl -s -X POST $BASE_URL/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"product_id\":\"$PRODUCT_ID\",\"quantity\":2}")

ORDER_ID=$(echo $ORDER | jq -r '.order.id')
echo "Order ID: $ORDER_ID  (status=pending, saga started...)"

# 5. Wait 2s for saga to complete, then check status
sleep 2
curl -s -H "Authorization: Bearer $TOKEN" $BASE_URL/api/orders/$ORDER_ID | jq

# 6. View payment records
curl -s -H "Authorization: Bearer $TOKEN" \
  "$BASE_URL/api/payments/order/$ORDER_ID" | jq
```

### Expected Saga Outcome

- **~90% of orders** will complete successfully (`status=completed`, `saga_state=payment_processed`)
- **~10% of orders** will fail due to simulated payment decline (`status=failed`, `saga_state=payment_failed_compensated`) with automatic inventory rollback

---

## RabbitMQ Management

Access the RabbitMQ management console at **http://localhost:15672** (user: `guest`, pass: `guest`) to:
- View the `saga.events` topic exchange
- Monitor message queues and rates
- Inspect message content

---

## Running Tests

### API Gateway (Node.js)

```bash
cd api-gateway
npm install
npm test
```

### Product Service (Node.js)

```bash
cd product-service
npm install
npm test
```

---

## Service Logs

```bash
# Follow all service logs
docker compose logs -f

# Follow specific service
docker compose logs -f order_service
docker compose logs -f payment_service
docker compose logs -f product_service
```

---

## Architecture Documentation

See [docs/architecture.md](docs/architecture.md) for:
- Detailed service architecture diagrams
- Complete Saga event flow
- Failure scenarios and compensation mechanisms
- Scalability strategies
- Full API reference

---

## Project Structure

```
KV_SSO_SAAS/
├── docker-compose.yml          # Orchestrates all services
├── api-gateway/                # Node.js API Gateway
│   ├── Dockerfile
│   ├── package.json
│   └── src/
│       ├── index.js            # Main gateway with JWT validation & routing
│       └── index.test.js       # Unit tests
├── user-service/               # PHP Lumen + MySQL
│   ├── Dockerfile
│   ├── app/Http/Controllers/UserController.php
│   ├── app/Models/User.php
│   ├── app/Http/Middleware/AuthMiddleware.php
│   └── database/migrations/
├── product-service/            # Node.js Express + MongoDB
│   ├── Dockerfile
│   └── src/
│       ├── index.js            # Express app
│       ├── models/Product.js   # Mongoose schema
│       ├── routes/products.js  # REST endpoints
│       └── consumers/orderConsumer.js  # Saga consumer
├── order-service/              # PHP Lumen + MySQL (Saga orchestrator)
│   ├── Dockerfile
│   └── app/
│       ├── Http/Controllers/OrderController.php
│       ├── Models/Order.php
│       ├── Services/SagaOrchestrator.php   # Publishes saga events
│       ├── Consumers/SagaConsumer.php      # Handles saga responses
│       └── Console/Commands/ConsumeSagaEvents.php
└── payment-service/            # Python FastAPI + PostgreSQL
    ├── Dockerfile
    ├── requirements.txt
    └── app/
        ├── main.py             # FastAPI application
        ├── models.py           # SQLAlchemy models
        ├── database.py         # DB configuration
        └── consumers/
            └── order_consumer.py  # Saga consumer (aio-pika)
```

---

## License

MIT
