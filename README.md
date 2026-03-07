# Microservices CRUD System

An event-driven, multi-language, multi-database microservices reference architecture implementing full CRUD operations with cross-service relationships, domain events, and transactional consistency.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        API Clients                               │
└──────────────────┬──────────────────────────────────────────────┘
                   │
    ┌──────────────▼──────────────┐    ┌──────────────────────────┐
    │  Service A: Product Service  │    │ Service B: Inventory Svc  │
    │  Laravel (PHP 8.3)           │    │ Node.js (Express)         │
    │  MySQL · Port 8080           │◄──►│ MongoDB · Port 3000       │
    └──────────────┬──────────────┘    └──────────────────────────┘
                   │ publishes                     │ subscribes
                   │                               │
    ┌──────────────▼──────────────────────────────▼─────────────┐
    │                    RabbitMQ (Port 5672)                     │
    │              Exchange: product_events (topic)               │
    │    product.created · product.updated · product.deleted      │
    └────────────────────────────────────────────────────────────┘
```

## Services

| Service | Technology | Database | Port |
|---------|-----------|----------|------|
| Product Service | Laravel 12 / PHP 8.3 | MySQL 8.0 | 8080 |
| Inventory Service | Node.js / Express | MongoDB 7.0 | 3000 |
| RabbitMQ | RabbitMQ 3.13 | — | 5672 / 15672 |

## Product Service – Modular Architecture

```
product-service/app/
├── Modules/
│   └── Product/
│       ├── Controllers/       # HTTP request/response only
│       │   └── ProductController.php
│       ├── Services/          # Business logic + transactions
│       │   ├── Contracts/ProductServiceInterface.php
│       │   └── ProductService.php
│       ├── Repositories/      # Eloquent DB interactions
│       │   ├── Contracts/ProductRepositoryInterface.php
│       │   └── ProductRepository.php
│       ├── Models/
│       │   └── Product.php    # Eloquent model with SoftDeletes
│       ├── Requests/
│       │   ├── StoreProductRequest.php
│       │   └── UpdateProductRequest.php
│       ├── Resources/
│       │   ├── ProductResource.php
│       │   └── ProductCollection.php
│       ├── Events/
│       │   ├── ProductCreated.php
│       │   ├── ProductUpdated.php
│       │   └── ProductDeleted.php
│       ├── Listeners/         # Queued RabbitMQ publishers
│       │   ├── PublishProductCreated.php
│       │   ├── PublishProductUpdated.php
│       │   └── PublishProductDeleted.php
│       ├── Routes/
│       │   └── api.php
│       └── Tests/
│           └── ProductApiTest.php
├── Services/
│   ├── RabbitMQService.php    # AMQP publisher
│   └── InventoryService.php   # HTTP client to Inventory Service
└── Providers/
    └── ProductServiceProvider.php   # DI bindings + event map
```

### Design Patterns

- **Controller → Service → Repository**: Controllers handle HTTP, Services contain business logic, Repositories manage DB.
- **Interface-based DI**: All services and repositories are bound through interfaces in `ProductServiceProvider`.
- **Domain Events**: `ProductCreated`, `ProductUpdated`, `ProductDeleted` are dispatched on mutations.
- **Queued Listeners**: Listeners implement `ShouldQueue` to publish events asynchronously to RabbitMQ.
- **DB Transactions**: All write operations (`create`, `update`, `delete`) are wrapped in `DB::transaction()`.
- **Compensating Transactions**: On product delete, inventory is deleted first inside the transaction; if the Inventory Service fails, the database transaction rolls back.

## Inventory Service – Architecture

```
inventory-service/src/
├── config/
│   ├── database.js          # MongoDB connection
│   └── rabbitmq.js          # AMQP connection with retry
├── controllers/
│   └── inventoryController.js
├── services/
│   └── inventoryService.js
├── repositories/
│   └── inventoryRepository.js
├── models/
│   └── Inventory.js         # Mongoose schema
├── routes/
│   └── inventoryRoutes.js
├── subscribers/
│   └── productEventSubscriber.js   # Consumes product_events
└── middleware/
    └── errorHandler.js
```

## API Reference

### Product Service (`:8080`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/products` | List products (with inventory) |
| POST | `/api/v1/products` | Create product |
| GET | `/api/v1/products/{id}` | Get product with inventory |
| PUT | `/api/v1/products/{id}` | Update product |
| DELETE | `/api/v1/products/{id}` | Delete product + cascade to inventory |

**Query Parameters** for `GET /api/v1/products`:
- `name` – filter by product name (partial match)
- `sku` – filter by SKU
- `per_page` – results per page (default 15)
- `page` – page number

**Create/Update Payload:**
```json
{
  "name": "Widget Pro",
  "description": "A fantastic widget",
  "price": 29.99,
  "stock_quantity": 100,
  "sku": "WGT-PRO-001"
}
```

### Inventory Service (`:3000`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/inventory` | List inventory (filter: `?product_name=...`) |
| POST | `/api/inventory` | Create inventory record |
| GET | `/api/inventory/{id}` | Get inventory item |
| PUT | `/api/inventory/{id}` | Update inventory item |
| DELETE | `/api/inventory/{id}` | Delete inventory item |
| GET | `/api/inventory/product/{name}` | Get inventory by product name |
| PUT | `/api/inventory/product/{name}` | Update inventory by product name |
| DELETE | `/api/inventory/product/{name}` | Delete inventory by product name |
| GET | `/health` | Health check |

## Event Flow

```
POST /api/v1/products
       │
       ▼
ProductController.store()
       │
       ▼
ProductService.createProduct()  ← DB::transaction()
       │
       ├─► ProductRepository.create()       (MySQL)
       │
       └─► event(new ProductCreated($product))
                   │
                   ▼
           PublishProductCreated (queued listener)
                   │
                   ▼
           RabbitMQService.publish('product.created', {...})
                   │
                   ▼
           RabbitMQ Exchange: product_events
                   │
                   ▼
           Inventory Service subscriber
                   │
                   ▼
           inventoryService.handleProductCreated()
                   │
                   ▼
           inventoryRepository.upsertByProductId()  (MongoDB)
```

## Transaction & Rollback Strategy

**On Product Delete:**
1. Begin MySQL transaction
2. Call Inventory Service HTTP API to delete related inventory
3. If Inventory Service returns failure → throw exception (502) → MySQL rolls back → 502 response
4. If Inventory Service succeeds → delete product from MySQL → commit
5. Dispatch `ProductDeleted` event → RabbitMQ notification (async, best-effort)

## Quick Start

### Prerequisites
- Docker & Docker Compose

### Run All Services

```bash
# Clone and start
git clone <repo-url>
cd CRUD

# Copy environment files
cp product-service/.env.example product-service/.env
# Set a unique APP_KEY:
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
# Add the output as APP_KEY in product-service/.env

# Start all services
docker compose up -d

# Run migrations
docker compose exec product-service php artisan migrate --force

# Check health
curl http://localhost:8080/api/v1/products
curl http://localhost:3000/health
```

### Access Services

| Service | URL |
|---------|-----|
| Product Service API | http://localhost:8080/api/v1/products |
| Inventory Service API | http://localhost:3000/api/inventory |
| RabbitMQ Management | http://localhost:15672 (guest/guest) |

## Testing

### Product Service (Laravel/PHPUnit)
```bash
cd product-service
php artisan test --filter ProductApiTest
```

### Inventory Service (Jest)
```bash
cd inventory-service
npm test
```

## Example Requests

```bash
# Create a product
curl -X POST http://localhost:8080/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Widget Pro","description":"A great widget","price":29.99,"stock_quantity":100,"sku":"WGT-001"}'

# List products with inventory data
curl http://localhost:8080/api/v1/products

# Get inventory for a product by name
curl http://localhost:3000/api/inventory/product/Widget%20Pro

# Update inventory by product name
curl -X PUT http://localhost:3000/api/inventory/product/Widget%20Pro \
  -H "Content-Type: application/json" \
  -d '{"quantity":150,"warehouse_location":"WH-A2"}'

# Delete product (cascades to inventory)
curl -X DELETE http://localhost:8080/api/v1/products/1
```

## SOLID Principles Applied

| Principle | Implementation |
|-----------|---------------|
| **S**ingle Responsibility | Controllers = HTTP only; Services = business logic; Repositories = DB only |
| **O**pen/Closed | New modules can be added without modifying existing code |
| **L**iskov Substitution | Implementations are swappable via interfaces |
| **I**nterface Segregation | Separate interfaces for Service and Repository layers |
| **D**ependency Inversion | Controllers depend on `ProductServiceInterface`, not the concrete class |