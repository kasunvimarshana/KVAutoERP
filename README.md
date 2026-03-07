# LaravelMSCRUD

A Laravel reference implementation of a **microservices CRUD** pattern using **Events and Listeners** for cross-service communication, database transactions for consistency, and rollback logic for error handling.

## Architecture Overview

The application models two logical microservices within a single Laravel project:

| Service | Responsibility |
|---------|---------------|
| **Service A — Product Service** | Full CRUD for `products`. Dispatches domain events on every mutation. |
| **Service B — Inventory Service** | Manages `inventories`. Reacts to Service A events to keep inventory in sync. |

Cross-service communication uses **Laravel Events & Listeners**:

```
Service A                          Service B
──────────────────────             ─────────────────────────────
ProductController                  HandleProductCreated  (creates inventory)
  │─ ProductCreated ──────────────►
  │─ ProductUpdated ──────────────► HandleProductUpdated (syncs product_name)
  └─ ProductDeleted ──────────────► HandleProductDeleted (removes inventory)
```

Each mutating operation in Service A is wrapped in a **database transaction**. Because listeners run synchronously by default (`QUEUE_CONNECTION=sync`), any failure in Service B propagates back and **rolls back** the Service A change — maintaining data consistency across services.

---

## Requirements

- PHP 8.2+
- Composer 2.x

---

## Setup

```bash
# 1. Clone the repository
git clone https://github.com/kasunvimarshana/LaravelMSCRUD.git
cd LaravelMSCRUD

# 2. Install dependencies
composer install

# 3. Configure environment (SQLite is used by default)
cp .env.example .env
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. (Optional) Seed with sample data
php artisan db:seed

# 6. Start the development server
php artisan serve
```

---

## API Endpoints

### Service A — Product Service (`/api/products`)

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/products` | List all products with their inventory |
| `POST` | `/api/products` | Create a product (dispatches `ProductCreated`) |
| `GET` | `/api/products/{id}` | Show a product with its inventory |
| `PUT` | `/api/products/{id}` | Update a product (dispatches `ProductUpdated`) |
| `DELETE` | `/api/products/{id}` | Delete a product (dispatches `ProductDeleted`) |

**Create / Update payload:**

```json
{
  "name": "Widget Pro",
  "description": "A high-quality widget",
  "price": 29.99,
  "sku": "WP-0001"
}
```

### Service B — Inventory Service (`/api/inventories`)

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/inventories` | List all inventories (supports `?product_name=` filter) |
| `GET` | `/api/inventories/{id}` | Show an inventory record with its product |
| `PUT` | `/api/inventories/{id}` | Update an inventory record by ID |
| `PATCH` | `/api/inventories/by-product-name` | Update inventory record(s) by product name |
| `DELETE` | `/api/inventories/{id}` | Delete an inventory record |

**Update inventory payload:**

```json
{
  "quantity": 100,
  "warehouse_location": "Main Warehouse",
  "status": "in_stock"
}
```

**Update by product name payload:**

```json
{
  "product_name": "Widget Pro",
  "status": "low_stock"
}
```

---

## Event-Driven Flow

### Product Created
1. `ProductController::store()` opens a DB transaction.
2. `Product::create()` inserts the product record.
3. `event(new ProductCreated($product))` is dispatched.
4. `HandleProductCreated::handle()` creates an `Inventory` record with `status = out_of_stock`.
5. Transaction commits. Both records are saved atomically.

### Product Updated
1. `ProductController::update()` opens a DB transaction.
2. `$product->update($validated)` updates the product.
3. `event(new ProductUpdated($product))` is dispatched.
4. `HandleProductUpdated::handle()` updates `product_name` in all related inventory records.
5. Transaction commits.

### Product Deleted
1. `ProductController::destroy()` captures `$productId` and `$productName`.
2. Opens a DB transaction, deletes the product.
3. `event(new ProductDeleted($productId, $productName))` is dispatched.
4. `HandleProductDeleted::handle()` deletes all inventory records for `product_id`.
5. Transaction commits. Both deletions are atomic.

### Rollback on Failure
If any listener throws an exception, the enclosing `DB::transaction()` in the controller is rolled back. The product change is **undone**, and an error response is returned to the caller.

---

## Key Source Files

```
app/
├── Events/
│   ├── ProductCreated.php          # Dispatched after product creation (Service A)
│   ├── ProductUpdated.php          # Dispatched after product update (Service A)
│   └── ProductDeleted.php          # Dispatched after product deletion (Service A)
├── Listeners/
│   ├── HandleProductCreated.php    # Creates inventory record (Service B)
│   ├── HandleProductUpdated.php    # Updates inventory product_name (Service B)
│   └── HandleProductDeleted.php    # Removes inventory records (Service B)
├── Http/
│   ├── Controllers/
│   │   ├── ProductController.php   # Service A endpoints
│   │   └── InventoryController.php # Service B endpoints
│   └── Resources/
│       ├── ProductResource.php     # JSON response with embedded inventory
│       └── InventoryResource.php   # JSON response with embedded product
├── Models/
│   ├── Product.php                 # Service A model
│   └── Inventory.php              # Service B model
└── Providers/
    └── AppServiceProvider.php      # Event ↔ Listener bindings
database/
├── migrations/
│   ├── 2024_01_01_000001_create_products_table.php
│   └── 2024_01_01_000002_create_inventories_table.php
├── factories/
│   ├── ProductFactory.php
│   └── InventoryFactory.php
└── seeders/
    └── DatabaseSeeder.php
routes/
└── api.php
tests/
└── Feature/
    ├── ProductServiceTest.php      # 13 tests for Service A
    └── InventoryServiceTest.php    # 11 tests for Service B
```

---

## Running Tests

```bash
php artisan test
```

Expected output: **24 tests, 111 assertions** — all passing.

---

## Queue Configuration

By default `QUEUE_CONNECTION=sync` (see `.env.example`), so listeners run synchronously inside the controller's transaction. To use asynchronous processing (e.g., Redis):

```env
QUEUE_CONNECTION=redis
```

Then start a worker:

```bash
php artisan queue:work --queue=inventory
```

> **Note:** With an async queue driver the transaction rollback across services is no longer automatic. Production systems should implement compensating transactions or a saga pattern for distributed consistency.
